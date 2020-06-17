<?php

namespace TTU\Charon\Services;

use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;

/**
 * Class TesterCommunicationService.
 *
 * @package TTU\Charon\Services
 */
class TesterCommunicationService
{
    /** @var HttpCommunicationService */
    private $httpCommunicationService;

    /**
     * TesterCommunicationService constructor.
     *
     * @param HttpCommunicationService $httpCommunicationService
     */
    public function __construct(HttpCommunicationService $httpCommunicationService)
    {
        $this->httpCommunicationService = $httpCommunicationService;
    }

    /**
     * Sends new Charon info to the tester.
     *
     * @param  Charon $charon
     * @param  string $unittestsGit
     * @param  string $courseShortName
     *
     * @return void
     */
    public function sendAddProjectInfo($charon, $unittestsGit, $courseShortName)
    {
        $params = [
            'id'           => $charon->id,
            'project'      => $charon->project_folder,
            'course'       => $courseShortName,
            'tester'       => $charon->testerType->name,
            'testerExtra'  => $charon->tester_extra,
            'systemExtra'  => $charon->system_extra,
            'unittestsUrl' => $unittestsGit,
            'gradeMaps'    => [],
        ];
        // TODO: Refactor to use grademaps instead of gradeMaps

        foreach ($charon->grademaps as $grademap) {
            $params['gradeMaps'][] = [
                'name'            => $grademap->name,
                'grade_type_name' => $grademap->getGradeTypeName(),
                'grade_type_code' => $grademap->grade_type_code,
            ];
        }

        $this->httpCommunicationService->postToTester('addproject', $params);
    }

    /**
     * Send git callback info to the tester.
     *
     * @param GitCallback $gitCallback
     * @param $testerCallbackUrl
     * @param $extraParameters
     */
    public function sendGitCallback(GitCallback $gitCallback, $testerCallbackUrl, $extraParameters)
    {
        $secret_token = $gitCallback->secret_token;
        $params = [
            'returnUrl' => $testerCallbackUrl,
            'token' => $secret_token,
            'return_extra' => ['token' => $secret_token]
        ];

        $params = array_merge($extraParameters, $params);

        $this->httpCommunicationService->postToTester('test', $params);
    }
}
