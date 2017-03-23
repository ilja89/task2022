<?php

namespace TTU\Charon\Http\Middleware;

use Closure;
use Zeizig\Moodle\Services\PermissionsService;

class RequireCourseManagement
{
    /** @var PermissionsService */
    private $permissionsService;

    /**
     * requireCourseManagement constructor.
     *
     * @param PermissionsService $permissionsService
     */
    public function __construct(PermissionsService $permissionsService)
    {
        $this->permissionsService = $permissionsService;
    }

    /**
     * Handle an incoming request.
     * Should only be used when there is a Course route model.
     * Ie. /courses/{course}/settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (config('app.env') === 'testing') {
            return $next($request);
        }

        $course = $request->route('course');
        require_login($course->id);
        $this->permissionsService->requireCourseManagementCapability($course->id);

        return $next($request);
    }
}
