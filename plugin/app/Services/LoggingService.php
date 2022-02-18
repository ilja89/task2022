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

    public function userHasQueryLoggingEnabled(int $userid): bool
    {
        $user = $this->loggingRepository->findUserWithQueryLoggingEnabled($userid);
        Log::debug($user);
        return (bool)$user;
    }

    public function getAllQueryLogsSingleFile(): string
    {
        return $this->logParseService->readLogs(true);
    }
}