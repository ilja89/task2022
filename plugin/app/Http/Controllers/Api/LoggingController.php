<?php

namespace TTU\Charon\Http\Controllers\Api;

use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Services\LoggingService;
use Illuminate\Http\Request;

class LoggingController extends Controller
{
    /** @var LoggingService */
    private $loggingService;

    /**
     * @param LoggingService $loggingService
     */
    public function __construct(Request $request, LoggingService $loggingService)
    {
        parent::__construct($request);
        $this->loggingService = $loggingService;
    }

    /**
     * @var $userId
     *
     * @return bool
     */
    public function userHasQueryLoggingEnabled($userId): bool
    {
        return $this->loggingService->userHasQueryLoggingEnabled($userId);
    }

    public function downloadQueryLogs()
    {
        $contents = $this->loggingService->getAllQueryLogsSingleFile();
        $filename = 'download.txt';
        return response()->streamDownload(function () use ($contents) {
            echo $contents;
        }, $filename);
    }
}