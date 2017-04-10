<?php

namespace TTU\Charon\Http\Middleware;

use Closure;
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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $courseId = $request->route('charon')->course;
        
        try {
            $this->permissionsService->requireCourseManagementCapability($courseId);
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
