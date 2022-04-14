<?php

namespace TTU\Charon\Services;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\PlagiarismService;

class PlagiarismCommunicationService
{
    /** @var HttpCommunicationService */
    private $httpCommunicationService;

    /**
     * PlagiarismCommunicationService constructor.
     *
     * @param HttpCommunicationService $httpCommunicationService
     */
    public function __construct(
        HttpCommunicationService $httpCommunicationService
    )
    {
        $this->httpCommunicationService = $httpCommunicationService;
    }

    /**
     * Send a request to create a checksuite for the given Charon with the given
     * parameters.
     *
     * @param Charon $charon
     * @param string[] $services
     * @param array $providers
     * @param string $includes
     *
     * @return \StdClass
     *
     * @throws GuzzleException
     */
    public function createChecksuite(Charon $charon, $services, $providers, $includes)
    {
        $plagiarismServices = PlagiarismService::whereIn('code', $services)->get();
        $data = [
            'name' => "charon__{$charon->id}__{$charon->name}",
            'resourceProviders' => Collection::make($providers)
                ->map(function ($provider) {
                    return [
                        'name' => 'git',
                        'configuration' => [
                            'repository' => $provider['repository'],
                            'privateKey' => $provider['private_key'],
                        ],
                    ];
                })
                ->toArray(),
            'plagiarismServices' => $plagiarismServices
                ->map(function ($service) {
                    return ['name' => $service->name];
                })
                ->toArray(),
            // TODO: Fix this when the field is correctly named 'includes'
            'regex' => $includes
        ];
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            'api/plagiarism/checksuite',
            'put',
            $data
        );

        return json_decode((string)$response->getBody());
    }

    /**
     * Send a request to the plagiarism service to run the given checksuite.
     *
     * @param string $checksuiteId
     *
     * @return \StdClass
     *
     * @throws GuzzleException
     */
    public function runChecksuite($checksuiteId)
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/plagiarism/checksuite/{$checksuiteId}/run",
            'post'
        );

        return json_decode((string)$response->getBody());
    }

    /**
     * Send a request to the plagiarism service to run check for the given charon.
     *
     * @param int $assignmentId
     * @param String $returnUrl
     * @return string
     *
     * @throws GuzzleException
     */
    public function runCheck(int $assignmentId, string $returnUrl): string
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/charon/assignment/{$assignmentId}/run-checksuite/",
            'POST',
            ["return_url" => $returnUrl]
        );
        if ($response instanceof GuzzleException) {
            if (strval($response->getCode())[0] === "4") {
                return "Could not connect to Plagiarism application";
            } else {
                return "Unexpected error";
            }
        }
        return $response->getBody()->getContents();
    }

    /**
     * Get the details about one checksuite.
     *
     * @param string $checksuiteId
     *
     * @return \StdClass
     *
     * @throws GuzzleException
     */
    public function getChecksuiteDetails($checksuiteId)
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/plagiarism/checksuite/{$checksuiteId}",
            'get'
        );

        return json_decode((string)$response->getBody());
    }

    /**
     * Get matches by plagiarism run.
     *
     * @param String $run_id
     * @return array
     *
     * @throws GuzzleException
     */
    public function getMatches(String $run_id): array
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/charon/run/{$run_id}/fetch-matches/",
            'GET'
        );

        if ($response instanceof GuzzleException) {
            throw $response;
        }
        return json_decode($response->getBody(), true);
    }


    /**
     * Get matches history times, when plagiarism was run for the given charon.
     *
     * @param int $assignmentId
     * @return array
     *
     * @throws GuzzleException
     */
    public function getMatchesHistoryTimes(int $assignmentId): array
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/charon/assignment/{$assignmentId}/run-times/",
            'GET'
        );

        if ($response instanceof GuzzleException) {
            throw $response;
        }
        return json_decode($response->getBody(), true);
    }

    /**
     * Get the details for one check.
     *
     * @param int $checkId
     *
     * @return \StdClass
     *
     * @throws GuzzleException
     */
    public function getCheckDetails($checkId)
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/plagiarism/similarity/check/{$checkId}",
            'get'
        );

        return json_decode((string)$response->getBody());
    }

    /**
     * Send data to Plagiarism and create or update a course
     * @throws GuzzleException
     */
    public function createOrUpdateCourse(array $courseSettings)
    {
        $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/charon/course/create-or-update/",
            "post",
            $courseSettings
        );
    }

    /**
     * Send data to Plagiarism and create or update an assignment
     * @param array $assignmentSettings array of settings needed to create or update an assignment in Plagiarism
     * @throws GuzzleException
     */
    public function createOrUpdateAssignment(array $assignmentSettings)
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/charon/assignment/create-or-update/",
            "post",
            $assignmentSettings
        );

        if ($response && $response->getBody()) {
            return json_decode($response->getBody());
        }
        return null;
    }

    /**
     * Fetch the course details from Plagiarism. If a response is received, mark the connection as true
     * @param $settings
     * @return \stdClass
     * @throws GuzzleException
     */
    public function getCourseDetails($settings): \stdClass
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/charon/course/details/",
            "get",
            $settings
        );

        if ($response) {
            $response = json_decode((string)$response->getBody());
            $response->plagiarism_connection = true;
            return $response;
        }

        $response = new \stdClass();
        $response->plagiarism_connection = false;
        return $response;
    }

    /**
     * Fetch the assignment details from Plagiarism. If Charon is being created, then we fetch the course details instead
     * If a response is received, mark the connection as true
     * @param $course
     * @param null $charon
     * @return \stdClass
     * @throws GuzzleException
     */
    public function getAssignmentDetails($course, $charon = null): \stdClass
    {
        if ($charon) {
            $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
                "api/charon/assignment/details/",
                "get",
                [
                    'course_name' => $course->shortname,
                    'assignment_name' => $charon->name
                ]
            );

            if ($response) {
                $response = json_decode((string)$response->getBody());
                $response->plagiarismConnection = true;
                return $response;
            }

            $response = new \stdClass();
            $response->plagiarismConnection = false;
            return $response;
        }

        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/charon/course/details/",
            "get",
            ['name' => $course->shortname]
        );

        if ($response) {
            $response = json_decode((string)$response->getBody());
            $response->plagiarismConnection = true;
            $response->assignmentExists = false;
            return $response;
        }

        $response = new \stdClass();
        $response->plagiarismConnection = false;
        return $response;
    }

    /**
     * Update the status of the given match and return the new status.
     *
     * @param int $matchId
     * @param string $newStatus
     * @return array|null
     *
     * @throws GuzzleException
     */
    public function updateMatchStatus(int $matchId, string $newStatus): ?array
    {
        $response = null;
        if ($newStatus == "plagiarism") {
            $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
                "api/plagiarism/match/{$matchId}/mark_plagiarism/",
                'put'
            );
        } else if ($newStatus == "acceptable") {
            $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
                "api/plagiarism/match/{$matchId}/mark_acceptable/",
                'put'
            );
        }

        return $response ? json_decode($response->getBody(), true) : null;
    }

    /**
     * Returns matches for the given user, if unable to respond returns empty object
     * @param string $uniid
     * @return mixed|\stdClass
     * @throws GuzzleException
     */
    public function getStudentMatches(string $uniid)
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/charon/studentMatches",
            'get',
            ["uniid" => $uniid]
        );

        if ($response) {
            return json_decode((string)$response->getBody());
        }

        return new \stdClass();
    }
}
