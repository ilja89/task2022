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
        $this->log($request);
    }

    protected function log($request)
    {
        $courseId = app(Course::class)->getCourseId();
        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($courseId);

        if (!$courseSettings || !$courseSettings->query_logging) {
            return;
        }

        $totalTime = 0;
        $username = app(User::class)->currentUser()->username;
        $url = $request->fullUrl();
        $method = $request->method();

        foreach (DB::getQueryLog() as $log) {
            $time = $log['time'];
            $totalTime += floatval($time);
        }

        $log = "DB query log\nUser: {$username}\nCourse: {$courseId}\nMethod & Url: [{$method} {$url}]\nTime: {$totalTime}ms\n";
        Log::channel('db')->debug($log);
    }
}
