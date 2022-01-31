<?php

namespace Tests\Unit\Services;

use Illuminate\Database\Eloquent\Model;
use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Dto\AreteRequestDto;
use TTU\Charon\Dto\SourceFileDTO;
use TTU\Charon\Http\Requests\CharonViewTesterCallbackRequest;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CourseSettings;
use TTU\Charon\Models\TesterType;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Services\GitCallbackService;
use TTU\Charon\Services\HttpCommunicationService;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Services\TesterCommunicationService;
use Zeizig\Moodle\Models\User;

class TesterCommunicationServiceTest extends TestCase
{
    /** @var GitCallback */
    private $gitCallback;

    /** @var Mock|HttpCommunicationService  */
    private $communicator;

    /** @var Mock|CharonRepository  */
    private $charonRepository;

    /** @var Mock|CourseSettingsRepository  */
    private $courseSettingsRepository;

    /** @var Mock|GitCallbackService  */
    private $gitCallbackService;

    /** @var TesterCommunicationService  */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gitCallback = new GitCallback(['secret_token' => 'Very secret token']);
        $this->communicator = Mockery::mock(HttpCommunicationService::class);
        $this->service = new TesterCommunicationService($this->communicator,
        $this->charonRepository = Mockery::mock(CharonRepository::class),
        $this->courseSettingsRepository = Mockery::mock(CourseSettingsRepository::class),
        $this->gitCallbackService = Mockery::mock(GitCallbackService::class));
    }

    public function testSendsGitCallbackWithoutExtra()
    {
        $this->communicator->shouldReceive('postToTester')->with([
            'returnUrl' => 'tester callback url',
            'returnExtra' => ['token' => 'Very secret token'],
            'regular' => 'param'
        ]);

        $this->service->sendGitCallback($this->gitCallback, 'tester callback url', ['regular' => 'param']);
    }

    public function testSendsGitCallbackWithExtra()
    {
        $this->communicator->shouldReceive('postToTester')->with([
            'returnUrl' => 'tester callback url',
            'returnExtra' => ['extra' => 'param', 'token' => 'Very secret token'],
            'regular' => 'param'
        ]);

        $this->service->sendGitCallback(
            $this->gitCallback,
            'tester callback url',
            ['regular' => 'param', 'returnExtra' => ['extra' => 'param']]
        );
    }

    public function testSendInfoToTesterSyncSuccessful()
    {
        $areteRequestDTO = Mockery::mock(AreteRequestDto::class);

        $params = ['requestInfo' => 'info'];
        $params2 = ['requestInfo' => 'info', 'returnUrl' => 'localhost'];

        $areteRequestDTO->shouldReceive('toArray')->once()->andReturn($params);

        $this->communicator
            ->shouldReceive('postToTesterSync')
            ->with($params2)
            ->once()
            ->andReturn(new CharonViewTesterCallbackRequest());

        $this->service->sendInfoToTesterSync($areteRequestDTO, 'localhost');
    }

    public function testPrepareAreteRequestSuccessful()
    {
        $charonId = 1;
        $charonCourse = 2;

        $correctResult = (new AreteRequestDto())
            ->setGitTestRepo('test@git.ssh')
            ->setTestingPlatform('python')
            ->setSlugs(['/path'])
            ->setSource([['path' => '/path/path', 'contents' => 'test']])
            ->setReturnExtra(["course" => 2, "usernames" => []])
            ->setUniid('user');

        $user = new User();
        $user->username = "user@ttu.ee";

        $courseSettings = new CourseSettings();
        $courseSettings->unittests_git = 'test@git.ssh';

        $charon = new Charon();
        $charon->course = $charonCourse;
        $testerType = new TesterType();
        $testerType->name = 'python';
        $charon->testerType = $testerType;
        $charon->project_folder = '/path';

        $this->charonRepository
            ->shouldReceive('getCharonById')
            ->once()
            ->with($charonId)
            ->andReturn($charon);

        $this->courseSettingsRepository
            ->shouldReceive('getCourseSettingsByCourseId')
            ->once()
            ->with($charonCourse)
            ->andReturn($courseSettings);

        $areteRequestDTO = $this->service
            ->prepareAreteRequest($charonId, $user, [['path' => 'path', 'content' => 'test']]);

        $this->assertEquals($correctResult, $areteRequestDTO);
    }

    public function testPrepareAreteRequestGroupSubmissionSuccessful()
    {
        $charonId = 1;
        $charonCourse = 2;
        $grouping_id = 3;

        $correctResult = (new AreteRequestDto())
            ->setGitTestRepo('test@git.ssh')
            ->setTestingPlatform('python')
            ->setSlugs(['/path'])
            ->setSource([['path' => '/path/path', 'contents' => 'test']])
            ->setReturnExtra(["course" => 2, "usernames" => [1 => 'teineuser@ttu.ee']])
            ->setUniid('user');

        $user = new User();
        $user->username = "user@ttu.ee";

        $courseSettings = new CourseSettings();
        $courseSettings->unittests_git = 'test@git.ssh';

        $charon = new Charon();
        $charon->course = $charonCourse;
        $testerType = new TesterType();
        $testerType->name = 'python';
        $charon->testerType = $testerType;
        $charon->grouping_id = $grouping_id;
        $charon->project_folder = '/path';

        $this->charonRepository
            ->shouldReceive('getCharonById')
            ->once()
            ->with($charonId)
            ->andReturn($charon);

        $this->courseSettingsRepository
            ->shouldReceive('getCourseSettingsByCourseId')
            ->once()
            ->with($charonCourse)
            ->andReturn($courseSettings);

        $this->gitCallbackService
            ->shouldReceive('getGroupUsers')
            ->with($grouping_id, 'user')
            ->once()
            ->andReturn(['user@ttu.ee', 'teineuser@ttu.ee']);

        $areteRequestDTO = $this->service
            ->prepareAreteRequest($charonId, $user, [['path' => 'path', 'content' => 'test']]);

        $this->assertEquals($correctResult, $areteRequestDTO);
    }
}
