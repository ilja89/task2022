<?php

namespace Tests\Feature\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CourseSettings;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\CourseModule;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Services\ModuleService;

class TesterControllerTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    /** @var int */
    private $charonModuleId;

    protected function setUp()
    {
        parent::setUp();

        Config::set('app.url', '');
        Carbon::setTestNow(Carbon::create(2020, 11, 16, 12));

        User::unguard();

        $this->charonModuleId = app(ModuleService::class)->getModuleId();
    }

    public function testPostingFromInlineSubmission()
    {
        /** @var User $user */
        $user = User::create(['username' => 'Sally']);

        /** @var Course $course */
        $course = Course::create(['shortname' => "iti0000-2222"]);

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create([
            'project_folder' => 'charon_folder',
            'course' => $course->id
        ]);

        $courseSettings = factory(CourseSettings::class)->create([
            'course_id' => $course->id,
            'unittests_git' => "tests"
        ]);

        $submissionFile = array("path"=>"exercise/ex.py", "content"=>"print('hello')");

        $response = $this->postJson('/api/submissions/' . $charon->id . '/postFromInline', [
            'userId' => $user->id,
            'sourceFiles' => [$submissionFile]
        ]);

        $response->assertStatus(200);
    }
}