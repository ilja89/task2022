<?php

namespace Tests\Unit\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;
use TTU\Charon\Events\GitCallbackReceived;
use TTU\Charon\Http\Requests\GithubCallbackPostRequest;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Repositories\GitCallbacksRepository;
use TTU\Charon\Repositories\UserRepository;
use TTU\Charon\Services\GitCallbackService;
use TTU\Charon\Exceptions\IncorrectSecretTokenException;

class GitCallbackServiceTest extends TestCase
{
    /** @var GitCallbackService */
    private $service;

    /** @var GitCallbacksRepository */
    private $repository;

    /* @var UserRepository */
    private $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(GitCallbacksRepository::class);
        $this->userRepository = Mockery::mock(UserRepository::class);
        $this->service = new GitCallbackService(
            $this->repository,
            Mockery::mock(CourseSettingsRepository::class),
            $this->userRepository);
    }

    public function testGetModifiedFilesReturnsEmptyIfNoCommits()
    {
        $actual = $this->service->getModifiedFiles([]);

        $this->assertEmpty($actual);
    }

    public function testGetModifiedFilesCollectsFilesFromAllCommits()
    {
        $commits = [
            [
                'added' => ['some/file.php'],
            ],
            [
                'modified' => ['some/file.php', 'changed/this/file/too.jpg'],
                'removed' => ['removed.exe']
            ]
        ];

        $actual = $this->service->getModifiedFiles($commits);

        $this->assertEquals(3, sizeof($actual));
        $this->assertContains('some/file.php', $actual);
        $this->assertContains('changed/this/file/too.jpg', $actual);
        $this->assertContains('removed.exe', $actual);
    }

    public function testFindCharonsReturnsEmptyWhenNoFiles()
    {
        $empty = $this->service->findCharons([], 1);

        $this->assertEmpty($empty);
    }

    public function testSaveCallbackForUserCreatesEvent()
    {
        Event::fake();

        $gitCallback = Mockery::mock(GitCallback::class);

        $this->repository
            ->shouldReceive('save')
            ->with('full url', 'repository url', 'username')
            ->andReturn($gitCallback);

        $this->service->saveCallbackForUser(
            'username@ttu.ee',
            'full url',
            'repository url',
            'callback url',
            ['provided' => 'by caller']
        );

        Event::assertDispatched(GitCallbackReceived::class, function ($event) use ($gitCallback) {
            return $event->gitCallback == $gitCallback
                && $event->testerCallbackUrl == 'callback url'
                && $event->requestData['uniid'] == 'username'
                && $event->requestData['gitStudentRepo'] == 'repository url'
                && $event->requestData['email'] == 'username@ttu.ee'
                && $event->requestData['provided'] == 'by caller';
        });
    }

    public function testChecksCorrectGitCallback()
    {
        $gitCallback = Mockery::mock(GitCallback::class)
                        ->shouldNotReceive('save')
                        ->getMock()
                        ->makePartial();

        $gitCallback->first_response_time = Carbon::now();

        $gitCallbackService = $this->getGitCallbackService('secret_token', $gitCallback);

        $gitCallbackService->checkGitCallbackForToken('secret_token');
    }

    public function testCheckGitCallbackSavesFirstResponseTime()
    {
        $gitCallback = Mockery::mock(GitCallback::class)
                        ->shouldReceive('save')
                        ->once()
                        ->getMock()
                        ->makePartial();

        $gitCallback->first_response_time = null;

        $gitCallbackService = $this->getGitCallbackService('secret_token', $gitCallback);

        $gitCallbackService->checkGitCallbackForToken('secret_token');
    }

    public function testCheckGitCallbackThrowsExceptionWhenTooOld()
    {
        $this->expectException(IncorrectSecretTokenException::class);

        $gitCallback = Mockery::mock(GitCallback::class)
                        ->shouldNotReceive('save')
                        ->getMock()
                        ->makePartial();

        $gitCallback->first_response_time = Carbon::now()->subMinutes(4);

        $gitCallbackService = $this->getGitCallbackService('secret_token', $gitCallback);

        $gitCallbackService->checkGitCallbackForToken('secret_token');
    }

    public function testCheckGitHubCallbackReturnsUserNotFound() {

        $request = Mockery::mock(GithubCallbackPostRequest::class);
        $request->shouldReceive('input')->with('repository')->andReturn(
            ['ssh_url' => 'repository url', 'owner' => ['email' => 'test@ttu.ee']]
        );
        $request->shouldReceive('getUriForPath')->with('/api/tester_callback')->andReturn('callback url');
        $request->shouldReceive('fullUrl')->andReturn('full url');

        $this->userRepository->shouldReceive('findByEmail')->with('test@ttu.ee')->andReturn(null);

        $response = $this->service->handleGitHubCallbackPost($request);

        $this->assertEquals('NO USER', $response);
    }

    private function getGitCallbackService($secretToken, $gitCallback)
    {
        return new GitCallbackService(
            Mockery::mock(GitCallbacksRepository::class)
             ->shouldReceive('findByToken')
             ->with($secretToken)
             ->once()
             ->andReturn($gitCallback)
             ->getMock(),
            Mockery::mock(CourseSettingsRepository::class),
            Mockery::mock(UserRepository::class)
        );
    }
}
