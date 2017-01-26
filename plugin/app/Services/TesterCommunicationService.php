<?php

namespace TTU\Charon\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\Charon;
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
     *
     * @return void
     */
    public function sendAddProjectInfo($charon, $unittestsGit)
    {
        $params = [
            'id'           => $charon->id,
            'project'      => $charon->project_folder,
            'course'       => $charon->courseModule()->moodleCourse->shortname,
            'tester'       => $charon->testerType->name,
            'extra'        => $charon->extra,
            'unittestsUrl' => $unittestsGit,
            'gradeMaps'    => [],
        ];

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
        Log::info('Sending data to tester.', ['uri' => $testerUrl . '/' . $uri, 'data' => $data]);
        $client = new Client();
        try {
            $client->request($method, $testerUrl . '/' . $uri, ['json' => $data]);
        } catch (RequestException $e) {
            Log::error('Could not send info to tester to url ' . $testerUrl . '/' . $uri);
        }
    }
}
