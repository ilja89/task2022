<?php

namespace TTU\Charon\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Repositories\CourseSettingsRepository;
use Zeizig\Moodle\Globals\User;
use Zeizig\Moodle\Globals\Course;

class LogDatabaseQuery
{
    /**
     * @var CourseSettingsRepository
     */
    private $courseSettingsRepository;

    /**
     * LogDatabaseQuery constructor.
     *
     * @param CourseSettingsRepository $courseSettingsRepository
     */
    public function __construct(CourseSettingsRepository $courseSettingsRepository)
    {
        $this->courseSettingsRepository = $courseSettingsRepository;
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
        DB::enableQueryLog();
        return $next($request);
    }

    public function terminate($request, $response)
    {
        $courseId = app(Course::class)->getCourseId();
        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($courseId);

        if (!$courseSettings || !$courseSettings->query_logging) {
            DB::disableQueryLog();
            return;
        }
        $this->log($request, $courseId);
    }

    protected function log($request, $courseId)
    {
        $totalTime = 0;
        $username = app(User::class)->currentUser()->username;
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
