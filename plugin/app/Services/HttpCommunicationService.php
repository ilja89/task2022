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
    public function sendPlagiarismServiceRequest($uri, $method, $data)
    {
        $plagiarismUrl = $this->settingsService->getSetting(
            'mod_charon',
            'plagiarism_service_url'
        );
        Log::info('Sending data to plagiarism service.', [
            'uri' => $plagiarismUrl . '/' . $uri,
            'data' => $data,
        ]);

        $client = new Client();
        try {
            return $client->request(
                $method,
                $plagiarismUrl . '/' . $uri,
                ['json' => $data]
            );
        } catch (RequestException $e) {
            Log::error(
                'Could not send info to the plagiarism service to the url "'
                . $plagiarismUrl . '/' . $uri . '".',
                ['error' => $e]
            );
        }
    }
}
