<?php

namespace TTU\Charon\Services;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\PlagiarismService;
use Zeizig\Moodle\Models\Course;

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
        $courseShortname = str_replace(' ', '-', trim($courseSettings['name']));
        $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/charon/course/" . $courseShortname . "/create-or-update/",
            "post",
            $courseSettings
        );
    }

    /**
     * Send data to Plagiarism and create or update a course
     * @param array $assignmentSettings array of settings needed to create or update an assignment in Plagiarism
     * @throws GuzzleException
     */
    public function createOrUpdateAssignment(array $assignmentSettings, string $courseName)
    {
        $courseShortname = str_replace(' ', '-', trim($courseName));
        $charonName = str_replace(' ', '-', trim($assignmentSettings['name']));
        $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/charon/course/" . $courseShortname . "/assignmentPath/" . $charonName . "/create-or-update/",
            "post",
            $assignmentSettings
        );
    }

    /**
     * @param Course $course
     * @return \stdClass
     * @throws GuzzleException
     */
    public function getCourseDetails(Course $course): \stdClass
    {
        $courseShortname = str_replace(' ', '-', trim($course->shortname));
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/charon/course/" . $courseShortname . "/course-details/",
            "get",
            []
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
     * @param Course $course
     * @param null $charon
     * @return \stdClass
     * @throws GuzzleException
     */
    public function getAssignmentDetails(Course $course, $charon = null): \stdClass
    {
        $courseShortname = str_replace(' ', '-', trim($course->shortname));
        if ($charon) {
            $charonName = str_replace(' ', '-', trim($charon->name));
            $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
                "api/charon/course/" . $courseShortname . "/assignmentPath/" . $charonName . "/assignment-details/",
                "get",
                []
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
            "api/charon/course/" . $courseShortname . "/course-details/",
            "get",
            []
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
}