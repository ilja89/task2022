<?php

namespace TTU\Charon\Services;

use GuzzleHttp\Exception\GuzzleException;

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
     * Send a request to the plagiarism service to run check for the given charon.
     *
     * @param array $data
     * @return array
     *
     * @throws GuzzleException
     */
    public function runCheck(array $data): array
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/charon/assignment/run-checksuite/",
            'POST',
            $data
        );
        if ($response instanceof GuzzleException) {
            throw $response;
        }
        return json_decode($response->getBody(), true);
    }

    /**
     * Send a request to the plagiarism service to save a new defense commit.
     *
     * @param array $data
     * @return void
     *
     * @throws GuzzleException
     */
    public function saveDefenseCommit(array $data)
    {
        $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/courses/commits/",
            'POST',
            $data
        );
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
     * If Charon is being created, then we fetch the course details instead.
     * If a response is received, mark the connection as true.
     *
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
     * @param string|null $comment
     * @param string $author
     * @return array|null
     *
     * @throws GuzzleException
     */
    public function updateMatchStatus(int $matchId, string $newStatus, ?string $comment, string $author): ?array
    {
        $response = null;
        if ($newStatus == "plagiarism") {
            if (!empty($comment)) {
                $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
                    "api/plagiarism/match/{$matchId}/mark_plagiarism/",
                    'put',
                    ['matchUpdate' => ['comment' => $comment, 'author' => $author]]
                );
            } else {
                $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
                    "api/plagiarism/match/{$matchId}/mark_plagiarism/",
                    'put',
                    ['matchUpdate' => null]
                );
            }

        } else if ($newStatus == "acceptable") {
            if (!empty($comment)) {
                $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
                    "api/plagiarism/match/{$matchId}/mark_acceptable/",
                    'put',
                    ['matchUpdate' => ['comment' => $comment, 'author' => $author]]
                );
            } else {
                $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
                    "api/plagiarism/match/{$matchId}/mark_acceptable/",
                    'put',
                    ['matchUpdate' => null]
                );
            }
        }
        return $response ? json_decode($response->getBody(), true) : null;
    }

    /**
     * Returns active matches for the given uniid, if unable to respond returns empty object
     * @param string $uniid
     * @param $plagiarismAssignmentIds
     * @return mixed|\stdClass
     * @throws GuzzleException
     */
    public function getStudentActiveMatches(string $uniid, $plagiarismAssignmentIds)
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/charon/studentActiveMatches/",
            "get",
            ['uniid' => $uniid, 'assignment_ids' => $plagiarismAssignmentIds]
        );

        if ($response) {
            return json_decode((string)$response->getBody());
        }

        return new \stdClass();
    }

    /**
     * Returns inactive matches for the given uniid, if unable to respond returns empty object
     * @param string $uniid
     * @param $plagiarismAssignmentIds
     * @return mixed|\stdClass
     * @throws GuzzleException
     */
    public function getStudentInactiveMatches(string $uniid, $plagiarismAssignmentIds)
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/charon/studentInactiveMatches/",
            "get",
            ['uniid' => $uniid, 'assignment_ids' => $plagiarismAssignmentIds]
        );

        if ($response) {
            return json_decode((string)$response->getBody());
        }

        return new \stdClass();
    }

    /**
     * @throws GuzzleException
     */
    public function getChecksByCourseSlug(string $courseSlug): ?array
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/charon/course/runs-history/",
            "get",
            ['course_name' => $courseSlug]
        );
        return $response ? json_decode($response->getBody(), true) : null;
    }

    /**
     * @param string $run_id
     * @return array|null
     * @throws GuzzleException
     */
    public function getLatestStatusByRunId(string $run_id): ?array
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/charon/course/runs-history/",
            "get",
            ['run_id' => $run_id]
        );
        return $response ? json_decode($response->getBody(), true) : null;
    }
}
