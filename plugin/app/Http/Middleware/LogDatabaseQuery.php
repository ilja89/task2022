<?php

namespace TTU\Charon\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Services\LoggingService;
use Zeizig\Moodle\Globals\User;
use Zeizig\Moodle\Globals\Course;

class LogDatabaseQuery
{
    /**
     * @var LoggingService
     */
    private $loggingService;

    /**
     * LogDatabaseQuery constructor.
     *
     * @param LoggingService $loggingService
     */
    public function __construct(LoggingService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = app(User::class)->currentUser();
        $courseId = app(Course::class)->getCourseId();
        $userEnabled = $this->loggingService->userHasQueryLoggingEnabled($user->id);

        if ($user) {
            $userEnabled = $this->loggingService->userHasQueryLoggingEnabled($user->id);
        } else {
            $userEnabled = null;
            Log::channel('db')->debug("Could not find the user making the request at URL: {$request->fullUrl()}");
        }

        if ($userEnabled) {
            DB::enableQueryLog();
        }

        $response = $next($request);

        if ($userEnabled) {
            $this->log($request, $courseId, $user->username);
        }

        return $response;
    }

    protected function log($request, $courseId, $username)
    {
        $totalTime = 0;
        $url = $request->fullUrl();
        $method = $request->method();

        $queryLog = "";
        foreach (array_values(DB::getQueryLog()) as $i => $log) {
            $time = fdiv($log['time'], 1000);
            $totalTime += $time;
            $index = $i+1;

            $queryLog .= "[{$index}] {$log['query']}\nTime: {$time}s\n---\n";
        }
        $finalLog = "User: {$username} | Total time: {$totalTime}s | Course: {$courseId} | Method & URL: [{$method} {$url}]\n";
        $finalLog .= $queryLog;
        Log::channel('db')->debug($finalLog);
        DB::disableQueryLog();
    }
}
