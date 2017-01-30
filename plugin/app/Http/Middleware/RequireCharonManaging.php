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
        $courseId = $request->route('charon')->courseModule()->course;
        $this->permissionsService->requireCourseManagementCapability($courseId);

        return $next($request);
    }
}
