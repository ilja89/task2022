<?php

namespace TTU\Charon\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogDatabaseQuery
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
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
        $totalTime = 0;
        $user = Auth::id();
        $url = $request->fullUrl();
        $method = $request->method();

        $startLog = "REQUEST START FOR USER: {$user} - {$method}@{$url}";
        Log::debug($startLog);

        foreach (DB::getQueryLog() as $log) {
            $time = $log['time'];
            $totalTime += floatval($time);
        }

        $endLog = "REQUEST END FOR USER: {$user} - TOTAL TIME: {$totalTime}ms\n";
        Log::debug($endLog);
    }
}
