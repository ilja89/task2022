<?php

namespace TTU\Charon\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\AuthenticationException;
use TTU\Charon\Exceptions\CourseManagementPermissionException;
use Zeizig\Moodle\Globals\User;
use Zeizig\Moodle\Services\PermissionsService;

class RequireCharonManaging
{
    /**
     * @var PermissionsService
     */
    private $permissionsService;

    /**
     * RequireCharonManaging constructor.
     *
     * @param PermissionsService $permissionsService
     */
    public function __construct(PermissionsService $permissionsService)
    {
        $this->permissionsService = $permissionsService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     * @throws CourseManagementPermissionException
     */
    public function handle($request, Closure $next)
    {
        Log::error('request');
        Log::error($request);
        Log::error($request->route('charon'));
        $courseId = $request->route('charon')->course;

        require_login($courseId);
        try {
            $this->permissionsService->requireCourseManagementCapability($courseId);
        } catch (\required_capability_exception $e) {
            throw new CourseManagementPermissionException(
                'course_management_permission_denied',
                app(User::class)->currentUserId(),
                $request->getClientIp(),
                $courseId
            );
        }

        return $next($request);
    }
}
