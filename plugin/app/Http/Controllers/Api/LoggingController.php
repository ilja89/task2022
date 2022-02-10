<?php

namespace TTU\Charon\Http\Controllers\Api;

use TTU\Charon\Services\LoggingService;

class LoggingController
{
    /** @var LoggingService */
    private $loggingService;

    /**
     * @param LoggingService $loggingService
     */
    public function __construct(LoggingService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    /**
     * @var $userId
     *
     * @return bool
     */
    public function userHasQueryLoggingEnabled($userId): bool
    {
        return $this->loggingService->userHasLoggingEnabled($userId);
    }

    public function downloadQueryLogs()
    {
        $contents = $this->loggingService->getAllQueryLogsSingleFile();
        $filename = 'download.txt';
        return response()->steamDownload(function () use ($contents) {
            echo $contents;
        }, $filename);
    }
}