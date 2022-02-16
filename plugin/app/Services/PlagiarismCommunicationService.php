<?php

namespace TTU\Charon\Services;

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
     * @throws \GuzzleHttp\Exception\GuzzleException
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

        return json_decode((string) $response->getBody());
    }

    /**
     * Send a request to the plagiarism service to run the given checksuite.
     *
     * @param string $checksuiteId
     *
     * @return \StdClass
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function runChecksuite($checksuiteId)
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/plagiarism/checksuite/{$checksuiteId}/run",
            'post'
        );

        return json_decode((string) $response->getBody());
    }

    /**
     * Send a request to the plagiarism service to run the given checksuite.
     *
     * @param string $checksuiteId
     *
     * @return \StdClass
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function runCheck($charon)
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/plagiarism/check",
            'post'
        );

        return json_decode((string) $response->getBody());
    }

    /**
     * Get the details about one checksuite.
     *
     * @param string $checksuiteId
     *
     * @return \StdClass
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getChecksuiteDetails($checksuiteId)
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/plagiarism/checksuite/{$checksuiteId}",
            'get'
        );

        return json_decode((string) $response->getBody());
    }

    /**
     * Get the details for one check.
     *
     * @param int $checkId
     *
     * @return \StdClass
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCheckDetails($checkId)
    {
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            "api/plagiarism/similarity/check/{$checkId}",
            'get'
        );

        return json_decode((string) $response->getBody());
    }
}