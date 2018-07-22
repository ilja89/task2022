<?php

namespace TTU\Charon\Services;

class PlagiarismCommunicationService
{
    /** @var PlagiarismCommunicationService */
    private $plagiarismCommunicationService;

    /**
     * PlagiarismCommunicationService constructor.
     *
     * @param  PlagiarismCommunicationService  $plagiarismCommunicationService
     */
    public function __construct(
        PlagiarismCommunicationService $plagiarismCommunicationService
    )
    {
        $this->plagiarismCommunicationService = $plagiarismCommunicationService;
    }
}