<?php

namespace TTU\Charon\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Repositories\CourseSettingsRepository;
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

    /** @var CourseSettingsRepository */
    private $courseSettingsRepository;

    /** @var GitCallbackService */
    private $gitCallbackService;

    /**
     * HttpCommunicator constructor.
     *
     * @param SettingsService $settingsService
     * @param CourseSettingsRepository $courseSettingsRepository
     * @param GitCallbackService $gitCallbackService
     */
    public function __construct(
        SettingsService $settingsService,
        CourseSettingsRepository $courseSettingsRepository,
        GitCallbackService $gitCallbackService)
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
    public function sendInfoToTester($method, $data)
    {
        /**
         * @var String $testerUrl
         * Initialize by default value
         */
        $testerUrl = $this->settingsService->getSetting(
            'mod_charon',
            'tester_url',
            'neti.ee' // This needs to be modified to the url that the tester is running on
            // for me currently: http://10.40.254.5:8080/services/arete/api/v2/submission/:testAsync
        );

        /**
         * @var String $testerToken
         * Initialize by default value
         */
        $testerToken = $this->settingsService->getSetting(
            'mod_charon',
            'tester_token',
            'charon'
        );

        $repo = $data['gitStudentRepo'];

        if ($repo) {
            Log::info("Repository found: '" . $repo . "'");
            $course = $this->gitCallbackService->getCourse($repo);
            $settings = $this->courseSettingsRepository->getCourseSettingsByCourseId($course->id);

            if ($settings && $settings->tester_url) {
                $testerUrl = $settings->tester_url;
                Log::info("Custom tester url found: '" . $testerUrl . "'");
            }

            if ($settings && $settings->tester_token) {
                $testerToken = $settings->tester_token;
                Log::info("Custom tester token found: '" . $testerToken . "'");
            }
        }

        Log::info('Sending data to tester.', [
            'uri' => $testerUrl,
            'tester token' => $testerToken,
            'data' => $data,
        ]);

        $client = new Client();
        try {
            $client->request(
                $method, $testerUrl,
                ['headers' => [/* Here needs to be added an Authorization header to access the tester
                see: https://gitlab.cs.ttu.ee/ained/charon/-/issues/454#note_151343
                'Authorization' => 'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJqYW5hciIsInJvbGVzIjpbIlRFU1RFUiJdLCJpYXQiOjE2MjcyOT
                kwODIsImV4cCI6MTYyNzMwMjY4Mn0.mIrH4xYmYxyNABD7gSV3K08jBVMBHkUBr6XUrcegUgc',*/
                    'X-Testing-Token' => $testerToken], 'json' => $data,]
            );
        } catch (RequestException $exception) {
            $body = is_null($exception->getResponse()) ? '' : $exception->getResponse()->getBody();
            Log::error('Could not send info to tester to url ' . $testerUrl . ' with body:', [$body]);
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
    public function postToTester($data)
    {
        $this->sendInfoToTester('post', $data);
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
