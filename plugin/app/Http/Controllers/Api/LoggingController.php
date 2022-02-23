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
     *
     * @param $courseId
     * @param $userId
     * @return int
     */
    public function userHasQueryLoggingEnabled($courseId, $userId): int
    {
        return $this->loggingService->userHasQueryLoggingEnabled($userId);
    }

    /**
     * Enables logging for the given user
     * @param $courseId
     * @param $userId
     * @return void
     */
    public function enableLoggingForUser($courseId, $userId)
    {
        $this->loggingService->enableLogging($userId);
    }

    /**
     * Disables logging for the given user
     * @param $courseId
     * @param $userId
     * @return void
     */
    public function disableLoggingForUser($courseId, $userId)
    {
        $this->loggingService->disableLogging($userId);
    }
}