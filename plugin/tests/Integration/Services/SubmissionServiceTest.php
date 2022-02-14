<?php

namespace Tests\Integration\Services;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\SubmissionFile;
use TTU\Charon\Models\Submission;
use TTU\Charon\Services\SubmissionService;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\User;

class SubmissionServiceTest extends TestCase
{
    use DatabaseTransactions;

    /** @var SubmissionService */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(SubmissionService::class);
    }

    public function testSaveFilesHandlesEmptyContents()
    {
        /** @var Submission $submission */
        $submission = factory(Submission::class)->create();

        $request = [
            ['path' => 'EX01/1.java', 'contents' => 'hello world'],
            ['path' => 'EX01/2.java', 'contents' => ''],
            ['path' => 'EX01/3.java', 'contents' => null],
            ['path' => 'EX01/4.java']
        ];

        $this->service->saveFiles($submission->id, $request);

        $files = SubmissionFile::where('submission_id', $submission->id)
            ->orderBy('path', 'asc')
            ->get()
            ->values()
            ->all();

        $this->assertEquals(4, sizeof($files));

        $this->assertEquals('hello world', $files[0]->contents);
        $this->assertEquals('', $files[1]->contents);
        $this->assertEquals('', $files[2]->contents);
        $this->assertEquals('', $files[3]->contents);
    }

    public function testSaveSubmissionWithGitCallback()
    {
        $request = new Request([
            'slug' => 'folder',
            'hash' => 'secrethash',
            'output' => 'output',
            'message' => 'first push',
            'consoleOutputs' => 'alloutputs'
        ]);

        /** @var GitCallback $gitCallback */
        $gitCallback = GitCallback::create([
            'url' => 'fullurl',
            'repo' => 'git@github.com:GitJupats/iti-000000.git',
            'user' => 'uniid',
            'created_at' => '00:00:00',
            'secret_token' => 'secretsecret'
        ]);

        /** @var Course $course */
        $course = factory(Course::class)->create(['shortname' => 'iti-000000']);

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create(['course' => $course->id, 'project_folder' => 'folder']);

        /** @var User $charon */
        $user = User::create([
            'username' => 'username@ttu.ee'
        ]);

        $submission = $this->service->saveSubmission($request, $gitCallback, $user->id);

        $this->assertEquals($charon->id, $submission->charon->id);
        $this->assertEquals('secrethash', $submission->git_hash);
        $this->assertEquals('output', $submission->mail);
        $this->assertEquals('alloutputs', $submission->stdout);
        $this->assertEquals('first push', $submission->git_commit_message);
        $this->assertEquals($gitCallback->id, $submission->git_callback_id);
        $this->assertEquals($user->id, $submission->user_id);
    }

    public function testSaveSubmissionWithOutGitCallback()
    {
        $request = new Request([
            'slug' => 'folder',
            'hash' => 'secrethash',
            'output' => 'output',
            'message' => 'first push',
            'consoleOutputs' => 'alloutputs'
        ]);

        /** @var Course $course */
        $course = factory(Course::class)->create(['shortname' => 'iti-000000']);

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create(['course' => $course->id, 'project_folder' => 'folder']);

        /** @var User $charon */
        $user = User::create([
            'username' => 'username@ttu.ee'
        ]);

        $submission = $this->service->saveSubmission($request, new GitCallback(), $user->id, $course->id);

        $this->assertEquals($charon->id, $submission->charon->id);
        $this->assertEquals('secrethash', $submission->git_hash);
        $this->assertEquals('output', $submission->mail);
        $this->assertEquals('alloutputs', $submission->stdout);
        $this->assertEquals('first push', $submission->git_commit_message);
        $this->assertEquals(null, $submission->git_callback_id);
        $this->assertEquals($user->id, $submission->user_id);
    }

    public function testAddNewEmptySubmission() {
        /** @var Course $course */
        $course = factory(Course::class)->create(['shortname' => 'iti-000000']);

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create(['course' => $course->id, 'project_folder' => 'folder']);

        /** @var User $charon */
        $user = User::create([
            'username' => 'username@ttu.ee'
        ]);

        $submission = $this->service->addNewEmptySubmission($charon, $user->id);

        $this->assertEquals($charon->id, $submission->charon->id);
        $this->assertEquals('', $submission->git_hash);
        $this->assertEquals('Manually created by teacher', $submission->stdout);
        $this->assertEquals(null, $submission->git_callback_id);
        $this->assertEquals($user->id, $submission->user_id);
    }
}
