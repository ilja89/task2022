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
use TTU\Charon\Http\Requests\GithubCallbackPostRequest;
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
            $this->service,
            $this->userRepository,
            $this->settingsRepository
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

        $this->service->shouldReceive('handleGitLabCallbackPost')->andReturn('NO COURSE');

        $response = $this->controller->indexPost($request);

        $this->assertEquals('NO COURSE', $response);
    }

    public function testGitHubIndexPostSavesNoCourse()
    {
        $request = $this->createCommonGitHubRequest();

        $this->service->shouldReceive('handleGitHubCallbackPost')->andReturn('NO COURSE');

        $response = $this->controller->gitHubIndexPost($request);

        $this->assertEquals('NO COURSE', $response);
    }

    public function testIndexPostSavesNoCharon()
    {
        $request = $this->createCommonRequest();

        $this->service->shouldReceive('handleGitLabCallbackPost')->andReturn('NO MATCHING CHARONS');

        $response = $this->controller->indexPost($request);

        $this->assertEquals('NO MATCHING CHARONS', $response);
    }

    public function testGitHubIndexPostSavesNoharon()
    {
        $request = $this->createCommonGitHubRequest();

        $this->service->shouldReceive('handleGitHubCallbackPost')->andReturn('NO MATCHING CHARONS');

        $response = $this->controller->gitHubIndexPost($request);

        $this->assertEquals('NO MATCHING CHARONS', $response);
    }

    public function testIndexPostSavesDifferentCharons()
    {
        $request = $this->createCommonRequest();

        $this->service->shouldReceive('handleGitLabCallbackPost')->andReturn('SUCCESS');

        $response = $this->controller->indexPost($request);

        $this->assertEquals('SUCCESS', $response);
    }

    public function testGitHubIndexPostSavesDifferentCharons()
    {
        $request = $this->createCommonGitHubRequest();

        $this->service->shouldReceive('handleGitHubCallbackPost')->andReturn('SUCCESS');

        $response = $this->controller->gitHubIndexPost($request);

        $this->assertEquals('SUCCESS', $response);
    }

    /**
     * @return MockInterface|GitCallbackPostRequest
     */
    private function createCommonRequest()
    {
        return Mockery::mock(GitCallbackPostRequest::class);
    }

    /**
     * @return MockInterface|GithubCallbackPostRequest
     */
    private function createCommonGitHubRequest()
    {
        return Mockery::mock(GithubCallbackPostRequest::class);
    }



}
