<?php

namespace TTU\Charon\Services;

use TTU\Charon\Helpers\HttpCommunicator;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;

/**
 * Class TesterCommunicationService.
 *
 * @package TTU\Charon\Services
 */
class TesterCommunicationService
{
    /** @var HttpCommunicator */
    private $httpCommunicator;

    /**
     * TesterCommunicationService constructor.
     *
     * @param HttpCommunicator $httpCommunicator
     *
     * @internal param SettingsService $settingsService
     */
    public function __construct(HttpCommunicator $httpCommunicator)
    {
        $this->httpCommunicator = $httpCommunicator;
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
            'extra'        => $charon->extra,
            'unittestsUrl' => $unittestsGit,
            'gradeMaps'    => [],
        ];
        // TODO: Refactor to use grademaps instead of gradeMaps

        foreach ($charon->grademaps as $grademap) {
            $params['gradeMaps'][] = [
                'name'            => $grademap->name,
                'grade_type_name' => $grademap->gradeType->name,
                'grade_type_code' => $grademap->grade_type_code,
            ];
        }

        $this->httpCommunicator->sendInfoToTester('addproject', 'post', $params);
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
        $params = [
            'callback_url' => $testerCallbackUrl,
            'secret_token' => $gitCallback->secret_token,
        ];

        $params = array_merge($extraParameters, $params);

        $this->httpCommunicator->sendInfoToTester('test', 'post', $params);
    }
}
