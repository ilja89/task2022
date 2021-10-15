<?php

namespace TTU\Charon\Services;

use GuzzleHttp\Exception\GuzzleException;
use TTU\Charon\Dto\AreteRequestDto;
use TTU\Charon\Dto\SourceFileDTO;
use TTU\Charon\Http\Requests\CharonViewTesterCallbackRequest;
use TTU\Charon\Http\Requests\TesterCallbackRequest;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\CourseSettingsRepository;
use Zeizig\Moodle\Models\User;

/**
 * Class TesterCommunicationService.
 *
 * @package TTU\Charon\Services
 */
class TesterCommunicationService
{
    /** @var HttpCommunicationService */
    private $httpCommunicationService;

    /** @var CourseSettingsRepository */
    protected $courseSettingsRepository;

    /** @var CharonRepository */
    private $charonRepository;

    /** @var GitCallbackService */
    private $callbackService;

    /**
     * TesterCommunicationService constructor.
     *
     * @param HttpCommunicationService $httpCommunicationService
     * @param CharonRepository $charonRepository
     * @param CourseSettingsRepository $courseSettingsRepository
     * @param GitCallbackService $callbackService
     */
    public function __construct(
        HttpCommunicationService $httpCommunicationService,
        CharonRepository $charonRepository,
        CourseSettingsRepository $courseSettingsRepository,
        GitCallbackService $callbackService
    )
    {
        $this->httpCommunicationService = $httpCommunicationService;
        $this->courseSettingsRepository = $courseSettingsRepository;
        $this->charonRepository = $charonRepository;
        $this->callbackService = $callbackService;
    }

    /**
     * Send git callback info to the tester.
     *
     * @param GitCallback $gitCallback
     * @param $testerCallbackUrl
     * @param $params
     * @throws GuzzleException
     */
    public function sendGitCallback(GitCallback $gitCallback, $testerCallbackUrl, $params)
    {
        $secret_token = $gitCallback->secret_token;

        $params['returnUrl'] = $testerCallbackUrl;
        if (isset($params['returnExtra'])) {
            $params['returnExtra'] = array_merge($params['returnExtra'], ['token' => $secret_token]);
        } else {
            $params['returnExtra'] = ['token' => $secret_token];
        }

        $this->httpCommunicationService->postToTester($params);
    }

    /**
     * Send AreteRequestDTO info to the tester in a synchronous request.
     *
     * @param AreteRequestDto $areteRequestDto
     * @param $testerCallbackUrl
     *
     * @return TesterCallbackRequest|CharonViewTesterCallbackRequest
     *
     */
    public function sendInfoToTesterSync(AreteRequestDto $areteRequestDto, $testerCallbackUrl): TesterCallbackRequest
    {
        $params = $areteRequestDto->toArray();

        $params['returnUrl'] = $testerCallbackUrl;

        return $this->httpCommunicationService->postToTesterSync($params);
    }

    /**
     * Prepare AreteRequestDTO from students submission.
     *
     * @param Charon $charon
     * @param User $user
     * @param array $sourceFiles
     *
     * @return AreteRequestDto
     */
    public function prepareAreteRequest (Charon $charon, User $user, array $sourceFiles): AreteRequestDto
    {
        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($charon->course);

        $username = strtok($user->username, "@");
        if ($charon->grouping_id != null) {
            $associatedUsers = $this->callbackService->getGroupUsers($charon->grouping_id,
                $username);
            unset($associatedUsers[array_search($username, $associatedUsers)]);
        } else {
            $associatedUsers = [];
        }

        $finalListofSource = [];
        foreach ($sourceFiles as $sourceFile) {
            $finalFile = new SourceFileDTO();
            $finalFile->setPath($charon->project_folder . '/' . $sourceFile['path']);
            $finalFile->setContent($sourceFile['content']);
            array_push($finalListofSource, $finalFile->toArray());
        }

        $finalListofSlugs = [];
        array_push($finalListofSlugs, $charon->project_folder);

        return (new AreteRequestDto())
            ->setGitTestRepo($courseSettings->unittests_git)
            ->setDockerExtra($charon->tester_extra)
            ->setTestingPlatform($charon->testerType->name)
            ->setSlugs($finalListofSlugs)
            ->setSource($finalListofSource)
            ->setReturnExtra(["course" => $charon->course, "usernames" => $associatedUsers])
            ->setUniid($username);
    }
}
