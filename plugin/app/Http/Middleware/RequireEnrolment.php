<?php

namespace TTU\Charon\Http\Middleware;

use Closure;
use TTU\Charon\Repositories\CharonRepository;
use Zeizig\Moodle\Services\PermissionsService;

class RequireEnrolment
{
    /** @var CharonRepository */
    private $charonRepository;

    /** @var PermissionsService */
    private $permissionsService;

    /**
     * RequireEnrolment constructor.
     *
     * @param CharonRepository $charonRepository
     * @param PermissionsService $permissionsService
     */
    public function __construct(CharonRepository $charonRepository, PermissionsService $permissionsService)
    {
        $this->charonRepository = $charonRepository;
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
        $charon = $this->charonRepository->getCharonByCourseModuleId($request['id']);
        $this->permissionsService->requireEnrollmentToCourse($charon->courseModule()->course);

        return $next($request);
    }
}
