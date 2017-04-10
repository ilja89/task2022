<?php

namespace TTU\Charon\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use TTU\Charon\Exceptions\CourseManagementPermissionException;
use Zeizig\Moodle\Globals\User;
use Zeizig\Moodle\Services\PermissionsService;

class RequireCourseManaging
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
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     * @throws CourseManagementPermissionException
     */
    public function handle($request, Closure $next)
    {
        if (config('app.env') === 'testing') {
            return $next($request);
        }

        $course = $request->route('course');
        require_login($course->id);
        try {
            $this->permissionsService->requireCourseManagementCapability($course->id);
        } catch (\required_capability_exception $e) {
            throw new CourseManagementPermissionException(
                'course_management_permission_denied',
                app(User::class)->currentUserId(),
                $request->getClientIp(),
                $course->id
            );
        }

        return $next($request);
    }
}
