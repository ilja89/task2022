<?php

namespace TTU\Charon\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
use Zeizig\Moodle\Services\SettingsService;

/**
 * Class TesterCommunicationService.
 *
 * @package TTU\Charon\Services
 */
class TesterCommunicationService
{
    /** @var SettingsService */
    private $settingsService;

    /**
     * TesterCommunicationService constructor.
     *
     * @param SettingsService $settingsService
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
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

        $this->sendInfoToTester('addproject', 'post', $params);
    }

    /**
     * Sends info to the tester.
     *
     * @param  string $uri
     * @param  string $method
     * @param  array $data
     *
     * @return void
     */
    private function sendInfoToTester($uri, $method, $data)
    {
        $testerUrl = $this->settingsService->getSetting('mod_charon', 'tester_url', 'neti.ee');
        \Log::info('Sending data to tester.', ['uri' => $testerUrl . '/' . $uri, 'data' => $data]);
        $client = new Client();
        try {
            $client->request($method, $testerUrl . '/' . $uri, ['json' => $data]);
        } catch (RequestException $e) {
            \Log::error('Could not send info to tester to url ' . $testerUrl . '/' . $uri);
        }
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

        $this->sendInfoToTester('test', 'post', $params);
    }
}
