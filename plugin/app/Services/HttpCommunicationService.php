<?php

namespace TTU\Charon\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Http\Requests\CharonViewTesterCallbackRequest;
use TTU\Charon\Http\Requests\TesterCallbackRequest;
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
        $this->courseSettingsRepository = $courseSettingsRepository;
        $this->gitCallbackService = $gitCallbackService;
    }

    /**
     * Sends info to the tester.
     *
     * @param array $data
     *
     * @return TesterCallbackRequest
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendInfoToTester(array $data):TesterCallbackRequest
    {
        /**
         * @var String $testerUrl
         * Initialize by default value
         */
        $testerUrl = $this->settingsService->getSetting(
            'mod_charon',
            'tester_url',
            'http://neti.ee'
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

        $studentGitRepo = null;
        $course = null;
        if (isset($data['gitStudentRepo'])) {
            $studentGitRepo = $data['gitStudentRepo'];
        } else if (isset($data['returnExtra']['course'])){
            $course = $data['returnExtra']['course'];
        }

        if ($studentGitRepo or $course) {
            if (!$course) {
                Log::info("Repository found: '" . $studentGitRepo . "'");
                $course = $this->gitCallbackService->getCourse($studentGitRepo);
                $settings = $this->courseSettingsRepository->getCourseSettingsByCourseId($course->id);
            } else {
                $settings = $this->courseSettingsRepository->getCourseSettingsByCourseId($course);
            }

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

        try {
            $response = Http::withHeaders(['Authorization' => $testerToken])->post($testerUrl, $data);
            Log::info("Response" , ["status" => $response->status()
            , "body" => $response->json()]);
            if ($response->successful()) {
                if (!empty($response->body())) {
                    return CharonViewTesterCallbackRequest::create("", "POST",
                        json_decode($response->body(), true))
                        ->setStatus($response->status());
                } else {
                    return (new CharonViewTesterCallbackRequest())->setStatus(204);
                }
            }
        } catch (RequestException $exception) {
            $body = is_null($exception->getResponse()) ? '' : $exception->getResponse()->getBody();
            Log::error('Could not send info to tester to url ' . $testerUrl . ' with body:', [$body]);
        }
        return (new CharonViewTesterCallbackRequest())->setStatus(400);
    }

    /**
     * Wrapper for sendInfoToTester for easier calling.
     *
     * @param string $uri
     * @param array $data
     *
     * @return TesterCallbackRequest|CharonViewTesterCallbackRequest
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postToTester(array $data):TesterCallbackRequest
    {
        return $this->sendInfoToTester($data);
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
    public function sendPlagiarismServiceRequest(string $uri, string $method, array $data = [])
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
