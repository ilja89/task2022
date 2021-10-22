<?php

namespace Tests\Unit\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;
use TTU\Charon\Events\GitCallbackReceived;
use TTU\Charon\Http\Controllers\Api\GitCallbackController;
use Tests\TestCase;
use TTU\Charon\Http\Requests\GitCallbackPostRequest;
use TTU\Charon\Http\Requests\GitCallbackRequest;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CourseSettings;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\TesterType;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Repositories\GitCallbacksRepository;
use TTU\Charon\Repositories\UserRepository;
use TTU\Charon\Services\GitCallbackService;
use Zeizig\Moodle\Models\Course;

class GitCallbackControllerTest extends TestCase
{

    /** @var GitCallbackController */
    private $controller;

    /** @var GitCallbacksRepository */
    private $callbackRepository;

    /** @var CourseSettingsRepository */
    private $settingsRepository;

    /** @var GitCallbackService */
    private $service;

    /** @var UserRepository */
    private $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->callbackRepository = Mockery::mock(GitCallbacksRepository::class);
        $this->settingsRepository = Mockery::mock(CourseSettingsRepository::class);
        $this->service = Mockery::mock(GitCallbackService::class);
        $this->userRepository = Mockery::mock(UserRepository::class);

        $this->controller = new GitCallbackController(
            Mockery::mock(Request::class),
            $this->callbackRepository,
            $this->settingsRepository,
            $this->service,
            $this->userRepository
        );
    }

    public function testIndexFiresEvent()
    {
        Event::fake();

        $gitCallback = Mockery::mock(GitCallback::class);

        $request = Mockery::mock(GitCallbackRequest::class);
        $request->shouldReceive('fullUrl')->andReturn('full url');
        $request->shouldReceive('input')->with('repo')->andReturn('repository url');
        $request->shouldReceive('input')->with('user')->andReturn('username');
        $request->shouldReceive('getUriForPath')->with('/api/tester_callback')->andReturn('uri');
        $request->shouldReceive('all')->andReturn(['custom' => 'params']);

        $this->callbackRepository
            ->shouldReceive('save')
            ->with('full url', 'repository url', 'username')
            ->andReturn($gitCallback);

        $this->controller->index($request);

        Event::assertDispatched(GitCallbackReceived::class, function ($event) use ($gitCallback) {
            return $event->gitCallback == $gitCallback
                && $event->testerCallbackUrl == 'uri'
                && $event->requestData['custom'] == 'params';
        });
    }

    public function testIndexPostSavesNoCourse()
    {
        $request = $this->createCommonRequest();
        $request->shouldReceive('input')->with('commits')->andReturn(true);
        $request->shouldReceive('input')->with('commits.0.author.email')->andReturn('user@email.com');

        $this->service->shouldReceive('getCourse')->with('repository url')->andReturnNull();

        $this->service->shouldReceive('saveCallbackForUser')->with(
            'username',
            'full url',
            'repository url',
            'callback url',
            ['email' => 'user@email.com']
        );

        $response = $this->controller->indexPost($request);

        $this->assertEquals('NO COURSE', $response);
    }

    public function testIndexPostSavesNoCharon()
    {
        $request = $this->createCommonRequest();
        $request->shouldReceive('input')->with('commits')->andReturn(false);
        $request->shouldReceive('input')->with('commits', [])->andReturn(['commit files']);

        $course = factory(Course::class)->make(['id' => 1, 'shortname' => 'course name']);

        $this->service->shouldReceive('getCourse')->with('repository url')->andReturn($course);

        /** @var CourseSettings $settings */
        $settings = factory(CourseSettings::class)->make(['course_id' => 1, 'unittests_git' => 'unittest git', ]);
        $settings->testerType = factory(TesterType::class)->make(['name' => 'tester name']);

        $this->settingsRepository->shouldReceive('getCourseSettingsByCourseId')->with(1)->andReturn($settings);

        $this->service->shouldReceive('getModifiedFiles')->with(['commit files'])->andReturn(['file/name']);

        $this->service->shouldReceive('findCharons')->with(['file/name'], 1)->andReturnNull();

        $this->service->shouldReceive('saveCallbackForUser')->with(
            'username',
            'full url',
            'repository url',
            'callback url',
            ['gitTestRepo' => 'unittest git', 'testingPlatform' => 'tester name']
        );

        $response = $this->controller->indexPost($request);

        $this->assertEquals('NO MATCHING CHARONS', $response);
    }

    public function testIndexPostSavesDifferentCharons()
    {
        $request = $this->createCommonRequest();
        $request->shouldReceive('input')->with('commits')->andReturn(false);
        $request->shouldReceive('input')->with('commits', [])->andReturn(['commit files']);

        $course = factory(Course::class)->make(['id' => 1, 'shortname' => 'course name']);

        $this->service->shouldReceive('getCourse')->with('repository url')->andReturn($course);

        /** @var CourseSettings $settings */
        $settings = factory(CourseSettings::class)->make(['course_id' => 1, 'unittests_git' => 'unittest git']);
        $settings->testerType = factory(TesterType::class)->make(['name' => 'tester name']);

        $this->settingsRepository->shouldReceive('getCourseSettingsByCourseId')->with(1)->andReturn($settings);

        $this->service->shouldReceive('getModifiedFiles')->with(['commit files'])->andReturn(['file/name']);

        $charonParams = [
            'project_folder' => 'folder',
            'system_extra' => 'some,extras',
            'tester_extra' => 'tester extra',
            'docker_test_root' => 'test root',
            'docker_content_root' => 'content root',
            'docker_timeout' => 180,
        ];

        $charonNoGrouping = factory(Charon::class)->make(['id' => 3, 'grouping_id' => null] + $charonParams);
        $charonNoUsers = factory(Charon::class)->make(['id' => 5, 'grouping_id' => 11] + $charonParams);
        $charon = factory(Charon::class)->make(['id' => 7, 'grouping_id' => 13] + $charonParams);

        $charonNoGrouping->testerType = factory(TesterType::class)->make(['name' => 'other name']);
        $charonNoUsers->testerType = factory(TesterType::class)->make(['name' => 'other name']);
        $charon->testerType = factory(TesterType::class)->make(['name' => 'other name']);

        $this->service
            ->shouldReceive('findCharons')
            ->with(['file/name'], 1)
            ->andReturn([$charonNoGrouping, $charonNoUsers, $charon]);

        $expectedParams = [
            'gitTestRepo' => 'unittest git',
            'testingPlatform' => 'other name',
            'slugs' => ['folder'],
            'systemExtra' => ['some', 'extras'],
            'dockerExtra' => 'tester extra',
            'dockerTestRoot' => 'test root',
            'dockerContentRoot' => 'content root',
            'dockerTimeout' => 180,
            'returnExtra' => ['charon' => 3]
        ];

        $this->service->shouldReceive('getGroupUsers')->with(11, 'username')->andReturn([]);

        $this->service->shouldReceive('saveCallbackForUser')->with(
            'username',
            'full url',
            'repository url',
            'callback url',
            $expectedParams
        );

        $expectedParams['returnExtra']['charon'] = 5;

        $this->service->shouldReceive('saveCallbackForUser')->with(
            'username',
            'full url',
            'repository url',
            'callback url',
            $expectedParams
        );

        $this->service->shouldReceive('getGroupUsers')->with(13, 'username')->andReturn(['some', 'other', 'names']);

        $expectedParams['returnExtra']['usernames'] = ['some', 'other', 'names'];
        $expectedParams['returnExtra']['charon'] = 7;

        $this->service->shouldReceive('saveCallbackForUser')->with(
            'username',
            'full url',
            'repository url',
            'callback url',
            $expectedParams
        );

        $response = $this->controller->indexPost($request);

        $this->assertEquals('SUCCESS', $response);
    }

    /**
     * @return MockInterface|GitCallbackPostRequest
     */
    private function createCommonRequest()
    {
        $request = Mockery::mock(GitCallbackPostRequest::class);
        $request->shouldReceive('input')->with('repository')->andReturn(['git_ssh_url' => 'repository url']);
        $request->shouldReceive('input')->with('user_username')->andReturn('username');
        $request->shouldReceive('getUriForPath')->with('/api/tester_callback')->andReturn('callback url');
        $request->shouldReceive('fullUrl')->andReturn('full url');
        return $request;
    }

}
