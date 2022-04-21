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

    public function userHasQueryLoggingEnabled(int $userid): int
    {
        return $this->loggingRepository->findUserWithQueryLoggingEnabled($userid);
    }

    public function enableLogging($userId)
    {
        $this->loggingRepository->addUserToLogging($userId);
    }

    public function disableLogging($userId)
    {
        $this->loggingRepository->RemoveUserFromLogging($userId);
    }

    public function findUsersWithLogging()
    {
        return $this->loggingRepository->findUsersWithLogging();
    }
}