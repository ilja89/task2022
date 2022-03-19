<?php

namespace TTU\Charon\Services;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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
     * Get the GitLab groups of the superuser account which is configured in Moodle Plagiarism settings
     * @return object[]|null
     * @throws GuzzleException
     */
    public function fetchGitlabGroups(): ?array
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/plagiarism/fetch-gitlab-groups",
            "get",
            []
        );

        if ($response == null) {
            return null;
        }

        $gitlab_groups = json_decode((string)$response->getBody());

        return array_map(function ($group) {
            return (object)array('code' => $group->label, 'name' => $group->label);
        }, $gitlab_groups);
    }

    /**
     * Send data to Plagiarism and create or update a course
     * @throws GuzzleException
     */
    public function createOrUpdateCourse(array $courseSettings)
    {
        $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/courses/create-or-update-course/",
            "post",
            $courseSettings
        );
    }

    /**
     * Send data to Plagiarism and create or update a course
     * @throws GuzzleException
     */
    public function createOrUpdateAssignment(array $settings)
    {
        $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/courses/create-or-update-assignment/",
            "post",
            $settings
        );
    }

    /**
     * @param $courseData
     * @return bool
     * @throws GuzzleException
     */
    public function courseExists($courseData)
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/courses/course-exists/",
            "get",
            $courseData
        );

        if ($response) {
            $response = json_decode((string)$response->getBody());
        }
        return $response ?: false;
    }
}