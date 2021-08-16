<?php

namespace Tests\Integration\Services;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use TTU\Charon\Models\SubmissionFile;
use TTU\Charon\Models\Submission;
use TTU\Charon\Services\SubmissionService;

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
}
