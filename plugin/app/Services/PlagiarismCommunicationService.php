<?php

namespace TTU\Charon\Services;

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
}