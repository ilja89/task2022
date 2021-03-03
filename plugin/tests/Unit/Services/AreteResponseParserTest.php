<?php

namespace Tests\Unit\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Mockery\Mock;
use Illuminate\Http\Request;
use Tests\TestCase;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Services\GitCallbackService;
use TTU\Charon\Services\AreteResponseParser;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\User;

class AreteResponseParserTest extends TestCase
{
    /** @var CharonRepository|Mock */
    private $charonRepository;

    /** @var GitCallbackService|Mock */
    private $gitCallbackService;

    /** @var AreteResponseParser */
    private $service;

    protected function setUp()
    {
        parent::setUp();
        Course::unguard();
        Charon::unguard();
        User::unguard();

        $this->service = new AreteResponseParser(
            $this->charonRepository = Mockery::mock(CharonRepository::class),
            $this->gitCallbackService = Mockery::mock(GitCallbackService::class)
        );
    }

    public function testGetFileFromRequest()
    {
        $request = ['path' => 'EX01/directory/structure/file.java', 'contents' => 'hello world'];

        $file = $this->service->getFileFromRequest(1, $request, true);

        $this->assertEquals(1, $file->submission_id);
        $this->assertEquals('EX01/directory/structure/file.java', $file->path);
        $this->assertEquals('hello world', $file->contents);
    }

    public function testGetEmptyFileFromRequest()
    {
        $request = ['path' => 'EX01/directory/structure/file.java'];

        $file = $this->service->getFileFromRequest(1, $request, true);

        $this->assertEquals(1, $file->submission_id);
        $this->assertEquals('EX01/directory/structure/file.java', $file->path);
        $this->assertEquals('', $file->contents);
    }

    /**
     * @throws Exception
     */
    public function testGetSubmissionFromRequestThrowsWhenNoRegularCourse()
    {
        $this->expectException(ModelNotFoundException::class);

        $callback = new GitCallback(['repo' => 'iti0200-2020/task-folder.git']);

        $this->gitCallbackService
            ->shouldReceive('getCourse')
            ->andThrow(new ModelNotFoundException());

        $this->service->getSubmissionFromRequest(new Request(), $callback, 3);
    }

    /**
     * @throws Exception
     */
    public function testGetSubmissionFromRequestThrowsWhenNoCharonById()
    {
        $this->expectException(ModelNotFoundException::class);

        $charonBuilder = Mockery::mock(Builder::class);

        $this->gitCallbackService
            ->shouldReceive('getCourse')
            ->andReturn(new Course(['id' => 3]));

        $this->charonRepository
            ->shouldReceive('query->where')
            ->with([['id', 5]])
            ->andReturn($charonBuilder);

        $charonBuilder->shouldReceive('firstOrFail')->andThrow(new ModelNotFoundException());

        $callback = new GitCallback(['repo' => '']);
        $request = new Request(['returnExtra' => ['charon' => 5]]);

        $this->service->getSubmissionFromRequest($request, $callback, 7);
    }

    /**
     * @throws Exception
     */
    public function testGetSubmissionFromRequestThrowsWhenNoCharonByFolderAndCourse()
    {
        $this->expectException(ModelNotFoundException::class);

        $charonBuilder = Mockery::mock(Builder::class);

        $this->gitCallbackService
            ->shouldReceive('getCourse')
            ->andReturn(new Course(['id' => 3]));

        $this->charonRepository
            ->shouldReceive('query->where')
            ->with([['project_folder', 'folder'], ['course', 3]])
            ->andReturn($charonBuilder);

        $charonBuilder->shouldReceive('firstOrFail')->andThrow(new ModelNotFoundException());

        $callback = new GitCallback(['repo' => '']);
        $request = new Request(['slug' => 'folder']);

        $this->service->getSubmissionFromRequest($request, $callback, 7);
    }

    /**
     * @throws Exception
     */
    public function testGetSubmissionFromRequestReturnsSubmission()
    {
        $this->gitCallbackService
            ->shouldReceive('getCourse')
            ->andReturn(new Course(['id' => 3]));

        $this->charonRepository
            ->shouldReceive('query->where->firstOrFail')
            ->andReturn(new Charon(['id' => 5]));

        $callback = new GitCallback(['repo' => '']);

        $request = new Request([
            'slug' => 'folder',
            'uniid' => 7,
            'retest' => 1,
            'original_submission_id' => 11,
            'hash' => '3d0945ddd6a1',
            'timestamp' => 1605531600,
            'output' => 'user@email.org',
            'testSuites' => [
                [],
                ['unitTests' => [[]]],
                ['unitTests' => [['stackTrace' => 's1t1']]],
                ['unitTests' => [['stackTrace' => 's2t1'], ['stackTrace' => 's2t2']]],
                ['unitTests' => [['stackTrace' => 's3t1'], ['stackTrace' => 's3t2']]],
            ],
            'consoleOutputs' => 'console',
            'message' => 'commit message'
        ]);

        $now = Carbon::create(2020, 11, 16, 12);
        Carbon::setTestNow($now);

        $submission = $this->service->getSubmissionFromRequest($request, $callback, 7);

        $this->assertEquals(5, $submission->charon_id);
        $this->assertEquals(7, $submission->user_id);
        $this->assertEquals('3d0945ddd6a1', $submission->git_hash);
        $this->assertEquals(Carbon::createFromTimestamp(1605531600), $submission->git_timestamp);
        $this->assertEquals('user@email.org', $submission->mail);
        $this->assertEquals('s1t1\n\ns2t1\n\ns2t2\n\ns3t1\n\ns3t2\nconsole', $submission->stdout);
        $this->assertEquals('stderr', $submission->stderr);
        $this->assertEquals($now, $submission->created_at);
        $this->assertEquals($now, $submission->updated_at);
        $this->assertEquals(11, $submission->original_submission_id);
    }

    public function testGetResultFromRequest()
    {
        $request = [
            'grade_type_code' => 1,
            'percentage' => 50,
            'grade' => 50,
            'stdout' => 'stdout text',
            'stderr' => 'stderr text',
        ];

        $result = $this->service->getResultFromRequest(1, $request, 1);

        $this->assertEquals(1, $result->submission_id);
        $this->assertEquals(0, $result->calculated_result);
        $this->assertEquals(0.5, $result->percentage);
    }
}
