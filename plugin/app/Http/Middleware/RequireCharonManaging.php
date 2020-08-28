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
        Log::error('request');
        Log::error($request);
        Log::error($request->route('charon'));
        Log::error('wat???');
        //Log::error(get_class($request->route('charon')));
        if (is_int($request->route('charon'))) {
            Log::error('int found');
            $courseId = $this->charonRepository->getCharonById($request->route('charon'))->course;
        } else {
            Log::error('not int');
            $courseId = $request->route('charon')->course;
        }
        Log::info($courseId);

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
