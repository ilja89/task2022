<?php

namespace TTU\Charon\Services;

use TTU\Charon\Repositories\LoggingRepository;

class LoggingService
{
    /** @var LoggingRepository */
    private $loggingRepository;

    /**
     * @param LoggingRepository $loggingRepository
     */
    public function __construct(LoggingRepository $loggingRepository)
    {
        $this->loggingRepository = $loggingRepository;
    }

    public function userHasQueryLoggingEnabled(int $userid)
    {
        $user = $this->loggingRepository->findUserWithQueryLoggingEnabled($userid);
        return (bool)$user;
    }
}