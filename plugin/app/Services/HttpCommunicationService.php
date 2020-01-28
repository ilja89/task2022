<?php

namespace TTU\Charon\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Zeizig\Moodle\Services\SettingsService;

/**
 * Class HttpCommunicator.
 * Sends HTTP requests. Wrapper to make it easier to unit test classes which
 * send HTTP requests via Guzzle.
 *
 * @package TTU\Charon\Helpers
 */
class HttpCommunicationService
{
    /** @var SettingsService */
    private $settingsService;

    /**
     * HttpCommunicator constructor.
     *
     * @param  SettingsService  $settingsService
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Sends info to the tester.
     *
     * @param string $uri
     * @param string $method
     * @param array $data
     *
     * @return void
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendInfoToTester($uri, $method, $data)
    {
        $testerUrl = $this->settingsService->getSetting(
            'mod_charon',
            'tester_url',
            'http://neti.ee'
        );
        Log::info('Sending data to tester.', [
            'uri' => $testerUrl . '/' . $uri,
            'data' => $data,
        ]);

        $client = new Client();
        try {
            $client->request(
                $method, $testerUrl . '/' . $uri,
                ['json' => $data]
            );
        } catch (RequestException $e) {
            Log::error(
                'Could not send info to tester to url '
                . $testerUrl . '/' . $uri
            );
        }
        if (array_key_exists('repo', $data) && strpos($data['repo'], 'iti0102-2019') !== false) {
            Log::info("Sending to new tester");
            $serverUrl = '10.4.1.11:8098/test';
            $ndata = [];
            $ndata['gitStudentRepo'] = $data['repo'];
            $data['uniid'] = $data['user'];
            //$ndata['gitStudentRepo'] = str_replace('.git', '', $data['extra']['project']['git_http_url']);
            //$ndata['uniid'] = $data['extra']['user_username'];
            $ndata['token'] = $data['secret_token'];
            $ndata['returnExtra'] = ['token' => $data['secret_token']];
            $ndata['returnUrl'] = 'https://ained.ttu.ee/mod/charon/api/tester_new_callback';
            //$ndata['systemExtra'] = ['noMail'];
            $ndata['dockerExtra'] = ["stylecheck"];
            $ndata['testingPlatform'] = 'python';
            $ndata['gitTestSource'] = 'https://gitlab.cs.ttu.ee/iti0102-2019/ex';

            $client = new Client();
            try {
                $client->request(
                    $method, $serverUrl,
                    ['json' => $ndata]
                );
            } catch (RequestException $e) {
                Log::error(
                    'Could not send info to tester to url '
                    . $serverUrl
                );
            }

        }
    }

    /**
     * Wrapper for sendInfoToTester for easier calling.
     *
     * @param string $uri
     * @param array $data
     *
     * @return void
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postToTester($uri, $data)
    {
        $this->sendInfoToTester($uri, 'post', $data);
    }

    /**
     * Send a request to the plagiarism service. The URL of the service is
     * specified in Charon settings (on a site-wide, not a per-course basis).
     *
     * @param string $uri
     * @param string $method - 'post'/'get' or any method Guzzle accepts.
     * @param array $data
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendPlagiarismServiceRequest($uri, $method, $data = [])
    {
        $plagiarismUrl = $this->settingsService->getSetting(
            'mod_charon',
            'plagiarism_service_url'
        );
        $token = $this->settingsService->getSetting(
            'mod_charon',
            'plagiarism_service_auth_token'
        );
        $headers = ['Authorization' => "Bearer {$token}"];

        Log::info('Sending data to plagiarism service.', [
            'uri' => $plagiarismUrl . '/' . $uri,
            'data' => $data,
        ]);

        $client = new Client(['base_uri' => $plagiarismUrl]);
        try {
            return $client->request(
                $method,
                "/{$uri}",
                ['json' => $data, 'headers' => $headers]
            );
        } catch (RequestException $e) {
            Log::error(
                'Could not send info to the plagiarism service to the url "'
                . $plagiarismUrl . '/' . $uri . '".',
                ['error' => $e]
            );

            throw $e;
        }
    }
}
