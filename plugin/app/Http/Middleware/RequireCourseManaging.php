<?php

namespace TTU\Charon\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use TTU\Charon\Exceptions\CourseManagementPermissionException;
use TTU\Charon\Repositories\CharonRepository;
use Zeizig\Moodle\Globals\User;
use Zeizig\Moodle\Services\PermissionsService;

class RequireCourseManaging
{
    /** @var PermissionsService */
    private $permissionsService;

    /**
     * @var CharonRepository
     */
    private $charonRepository;

    /**
     * requireCourseManagement constructor.
     *
     * @param PermissionsService $permissionsService
     * @param CharonRepository $charonRepository
     */
    public function __construct(PermissionsService $permissionsService, CharonRepository $charonRepository)
    {
        $this->permissionsService = $permissionsService;
        $this->charonRepository = $charonRepository;
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

        try {
            $courseId = $request->route('course')->id;
        } catch (\Exception $e) {
            $courseId = $request->route('course');
        }

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
