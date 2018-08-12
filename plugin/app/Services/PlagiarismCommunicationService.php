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
     * @param string[] $includes
     *
     * @return \Psr\Http\Message\StreamInterface
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createChecksuite(Charon $charon, $services, $providers, $includes)
    {
        $plagiarismServices = PlagiarismService::whereIn('code', $services)
            ->get();
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
            'excludes' => $includes
        ];
        $response = $this->httpCommunicationService->sendPlagiarismServiceRequest(
            'api/plagiarism/checksuite',
            'put',
            $data
        );

        dd($response);

        return $response->getBody();
    }
}