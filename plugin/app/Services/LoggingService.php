<?php

namespace TTU\Charon\Services;

use Illuminate\Support\Facades\Log;
use TTU\Charon\Repositories\LoggingRepository;

class LoggingService
{
    /** @var LoggingRepository */
    private $loggingRepository;

    /** @var LogParseService */
    private $logParseService;

    /**
     * @param LoggingRepository $loggingRepository
     * @param LogParseService $logParseService
     */
    public function __construct(LoggingRepository $loggingRepository, LogParseService $logParseService)
    {
        $this->loggingRepository = $loggingRepository;
        $this->logParseService = $logParseService;
    }

    public function getAllQueryLogsSingleFile(): string
    {
        return $this->logParseService->readLogs(true);
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
}