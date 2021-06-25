<?php

namespace Tests\Feature\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Config;
use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\CourseModule;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Services\GradingService;
use Zeizig\Moodle\Services\ModuleService;

class TesterCallbackControllerTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    /** @var int */
    private $charonModuleId;

    /** @var Mock|GradingService */
    private $gradingService;

    /**
     * Mocking GradingService as it would try to include Moodle source which is not available
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->gradingService = Mockery::mock(GradingService::class);
        $this->app->instance(GradingService::class, $this->gradingService);

        Config::set('app.url', '');
        Carbon::setTestNow(Carbon::create(2020, 11, 16, 12));

        User::unguard();

        $this->charonModuleId = app(ModuleService::class)->getModuleId();
    }

    public function testIndexSavesStyleForSingleStudentSubmission()
    {
        // given

        $user = User::create(['username' => 'mindy']);

        /** @var GitCallback $callback */
        $callback = factory(GitCallback::class)->create([
            'secret_token' => 'test token',
            'repo' => 'iti0999-2222/exams',
            'user' => 'mindy'
        ]);

        $course = Course::create(['shortname' => 'iti0999-2222']);

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create([
            'project_folder' => 'charon_folder',
            'course' => $course->id
        ]);

        CourseModule::create([
            'instance' => $charon->id,
            'module' => $this->charonModuleId,
            'course' => $course->id
        ]);

        /** @var GradeItem $gradeItem */
        $gradeItem = factory(GradeItem::class, 'course_grade_item')->create([
            'courseid' => $course->id
        ]);

        Grademap::create([
            'charon_id' => $charon->id,
            'grade_type_code' => 101,
            'grade_item_id' => $gradeItem->id
        ]);

        $this->gradingService->shouldReceive('updateGrade');

        // when

        $response = $this->postJson('/api/tester_callback', [
            'slug' => 'charon_folder',
            'uniid' => 'mindy',
            'style' => 100,
            'timestamp' => Carbon::now()->getTimestamp(),
            'hash' => '5dba462b9ab77ac5dc158eb5047367f0',
            'commitMessage' => 'Made a new submission',
            'testSuites' => [],
            'returnExtra' => [
                'token' => 'test token'
            ]
        ]);

        // then

        $response->assertStatus(200);

        $this->gradingService
            ->shouldHaveReceived('updateGrade')
            ->with($course->id, $charon->id, 101, $user->id, 100)
            ->once();

        $callback->refresh();
        $this->assertEquals(Carbon::now(), $callback->first_response_time);

        /** @var Submission $submission */
        $submission = Submission::where('git_hash', '5dba462b9ab77ac5dc158eb5047367f0')->first();
        $this->assertEquals($user->id, $submission->user_id);
        $this->assertEquals($callback->id, $submission->git_callback_id);

        /** @var Result $styleResult */
        $styleResult = Result::where('submission_id', $submission->id)->where('grade_type_code', 101)->first();
        $this->assertEquals(1, $styleResult->percentage);
    }
}
