<?php

namespace TTU\Charon\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Zeizig\Moodle\Services\SettingsService;

/**
 * Class HttpCommunicator.
 * Sends HTTP requests. Wrapper to make it easier to unit test classes
 * which send HTTP requests via Guzzle.
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
     * @param  string  $uri
     * @param  string  $method
     * @param  array  $data
     *
     * @return void
     */
    public function sendInfoToTester($uri, $method, $data)
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

    /**
     * Wrapper for sendInfoToTester for easier calling.
     *
     * @param  string  $uri
     * @param  array  $data
     *
     * @return void
     */
    public function postToTester($uri, $data)
    {
        $this->sendInfoToTester($uri, 'post', $data);
    }
}
