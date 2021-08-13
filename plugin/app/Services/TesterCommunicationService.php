<?php

namespace TTU\Charon\Services;

use GuzzleHttp\Exception\GuzzleException;
use TTU\Charon\Dto\AreteRequestDto;
use TTU\Charon\Dto\SourceFileDTO;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Repositories\UserRepository;

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

    /** @var UserRepository */
    private $userRepository;

    /** @var GitCallbackService */
    private $callbackService;

    /**
     * TesterCommunicationService constructor.
     *
     * @param HttpCommunicationService $httpCommunicationService
     * @param CharonRepository $charonRepository
     * @param CourseSettingsRepository $courseSettingsRepository
     * @param UserRepository $userRepository
     * @param GitCallbackService $callbackService
     */
    public function __construct(
        HttpCommunicationService $httpCommunicationService,
        CharonRepository $charonRepository,
        CourseSettingsRepository $courseSettingsRepository,
        UserRepository $userRepository,
        GitCallbackService $callbackService
    )
    {
        $this->httpCommunicationService = $httpCommunicationService;
        $this->courseSettingsRepository = $courseSettingsRepository;
        $this->charonRepository = $charonRepository;
        $this->userRepository = $userRepository;
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
     * Send AreteRequestDTO info to the tester.
     *
     * @param AreteRequestDto $areteRequestDto
     * @param $testerCallbackUrl
     * @throws GuzzleException
     */
    public function sendInfoToTester(AreteRequestDto $areteRequestDto, $testerCallbackUrl) {
        $params = $areteRequestDto->toArray();

        $params['returnUrl'] = $testerCallbackUrl;

        $this->httpCommunicationService->postToTester($params);
    }

    /**
     * Prepare AreteRequestDTO from students submission.
     *
     * @param int $charonId
     * @param int $userId
     * @param array $sourceFiles
     *
     * @return AreteRequestDto
     */
    public function prepareAreteRequest (int $charonId, int $userId, array $sourceFiles): AreteRequestDto
    {
        $charon = $this->charonRepository->getCharonById($charonId);

        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($charon->course);

        $user = $this->userRepository->find($userId);
        $username = strtok($user->username, "@");
        if ($charon->grouping_id != null) {
            $associatedUsers = $this->callbackService->getGroupUsers($charon->grouping_id,
                $username);
        } else {
            $associatedUsers = [];
        }

        $finalListofSource = [];
        foreach ($sourceFiles as $sourceFile) {
            $finalFile = new SourceFileDTO();
            $finalFile->setPath($sourceFile->path);
            $finalFile->setContent($sourceFile->content);
            array_push($finalListofSource, $finalFile->toArray());
        }

        $finalListofSlugs = [];
        array_push($finalListofSlugs, $charon->project_folder);

        return (new AreteRequestDto())
            ->setGitTestRepo($courseSettings->unittests_git)
            ->setTestingPlatform($charon->testerType->name)
            ->setSlugs($finalListofSlugs)
            ->setSource($finalListofSource)
            ->setReturnExtra(["course" => $charon->course, "usernames" => $associatedUsers])
            ->setUniid($username);
    }
}
