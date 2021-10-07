<?php

namespace Tests\Integration\Services\Flows;

use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use TTU\Charon\Http\Requests\TesterCallbackRequest;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Services\Flows\SaveTesterCallback;
use Tests\TestCase;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Services\GradingService;

class SaveTesterCallbackTest extends TestCase
{
    use DatabaseTransactions;

    /** @var GradingService */
    private $gradingService;

    /** @var SaveTesterCallback */
    private $flow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gradingService = Mockery::mock(GradingService::class)->makePartial();

        $this->app->bind(GradingService::class, function() { return $this->gradingService; });

        $this->flow = $this->app->make(SaveTesterCallback::class);
    }

    /**
     * @throws Exception
     */
    public function testRun()
    {
        GradeItem::unguard();

        /** @var User $mainStudent */
        $mainStudent = factory(User::class)->create(['username' => 'main@ttu.ee']);

        /** @var User $coStudent */
        $coStudent = factory(User::class)->create(['username' => 'co@ttu.ee']);

        /** @var GitCallback $gitCallback */
        $gitCallback = factory(GitCallback::class)->create(['repo' => 'git@gitlab.cs.ttu.ee:username/direct-match.git']);

        /** @var Course $course */
        $course = factory(Course::class)->create(['shortname' => 'direct-match']);

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create(['course' => $course->id, 'project_folder' => 'folder']);

        factory(Grademap::class)->create(['charon_id' => $charon->id, 'grade_item_id' => GradeItem::create(['grademax' => 1])->id, 'grade_type_code' => 1]);
        factory(Grademap::class)->create(['charon_id' => $charon->id, 'grade_item_id' => GradeItem::create(['grademax' => 1])->id, 'grade_type_code' => 101]);
        factory(Grademap::class)->create(['charon_id' => $charon->id, 'grade_item_id' => GradeItem::create(['grademax' => 1])->id, 'grade_type_code' => 1001]);

        $this->gradingService->shouldReceive('updateGrade')->once()->with($course->id, $charon->id, 1, $mainStudent->id, 0.5);
        $this->gradingService->shouldReceive('updateGrade')->once()->with($course->id, $charon->id, 101, $mainStudent->id, 1.0);
        $this->gradingService->shouldReceive('updateGrade')->once()->with($course->id, $charon->id, 1001, $mainStudent->id, 0);
        $this->gradingService->shouldReceive('updateGrade')->once()->with($course->id, $charon->id, 1, $coStudent->id, 0.5);
        $this->gradingService->shouldReceive('updateGrade')->once()->with($course->id, $charon->id, 101, $coStudent->id, 1.0);
        $this->gradingService->shouldReceive('updateGrade')->once()->with($course->id, $charon->id, 1001, $coStudent->id, 0);

        $request = new TesterCallbackRequest([
            'slug' => 'folder',
            'uniid' => 7,
            'retest' => 0,
            'hash' => '3d0945ddd6a1',
            'timestamp' => 1605531600,
            'output' => 'user@email.org',
            'consoleOutputs' => 'console',
            'message' => 'commit message',
            'style' => 100,
            'files' => [
                ['path' => 'temp.txt', 'contents' => 'temp'],
                ['path' => 'file.txt', 'contents' => 'file']
            ],
            'testSuites' => [
                [
                    'name' => 'suite',
                    'file' => 'file',
                    'weight' => null,
                    'passedCount' => 1,
                    'grade' => 50,
                    'unitTests' => [
                        [
                            'groupsDependedUpon' => '',
                            'status' => 'closed',
                            'weight' => 1,
                            'printExceptionMessage' => 0,
                            'printStackTrace' => 0,
                            'timeElapsed' => 160,
                            'methodsDependedUpon' => '',
                            'stackTrace' => null,
                            'name' => 'unit',
                            'stdout' => null,
                            'exceptionClass' => 'exception',
                            'exceptionMessage' => 'message',
                            'stderr' => ''
                        ]
                    ]
                ]
            ],
        ]);

        $submission = $this->flow
            ->saveTestersAsyncResponse($request, $gitCallback, ['main', 'main@ttu.ee', 'co@ttu.ee']);

        $submission->refresh();

        $this->assertEquals($gitCallback->id, $submission->git_callback_id);
        $this->assertEquals($charon->id, $submission->charon_id);

        $this->assertEquals(
            [$mainStudent->id, $coStudent->id],
            collect($submission->users)->pluck('id')->values()->all()
        );

        $this->assertEquals(6, $submission->results->count());

        $this->assertEquals(
            [1 => 0.5, 101 => 1, 1001 => 0],
            $submission->results->pluck('percentage', 'grade_type_code')->all()
        );

        $this->assertEquals(
            [$mainStudent->id, $coStudent->id],
            $submission->results->pluck('user_id')->unique()->values()->all()
        );

        $this->assertEquals(
            ['temp.txt' => 'temp', 'file.txt' => 'file'],
            $submission->files->pluck('contents', 'path')->all()
        );
    }
}
