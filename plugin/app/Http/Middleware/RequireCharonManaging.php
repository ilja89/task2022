<?php

namespace TTU\Charon\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\AuthenticationException;
use TTU\Charon\Exceptions\CourseManagementPermissionException;
use TTU\Charon\Repositories\CharonRepository;
use Zeizig\Moodle\Globals\User;
use Zeizig\Moodle\Services\PermissionsService;

class RequireCharonManaging
{
    /**
     * @var PermissionsService
     */
    private $permissionsService;

    /**
     * @var CharonRepository
     */
    private $charonRepository;

    /**
     * RequireCharonManaging constructor.
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
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     * @throws CourseManagementPermissionException
     */
    public function handle($request, Closure $next)
    {
        try {
            $courseId = $this->charonRepository->getCharonById(intval($request->route('charon')))->course;
        } catch (\Exception $e) {
            $courseId = $request->route('charon')->course;
        }

        require_login($courseId);
        try {
            $this->permissionsService->requireCourseManagementCapability();
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
