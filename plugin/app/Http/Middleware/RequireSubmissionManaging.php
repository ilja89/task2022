<?php

namespace TTU\Charon\Http\Middleware;

use Closure;
use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Services\PermissionsService;

class RequireSubmissionManaging
{
    /** @var PermissionsService */
    private $permissionsService;

    /**
     * RequireSubmissionManaging constructor.
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
        if (config('app.env') === 'testing') {
            return $next($request);
        }

        /** @var Submission $submission */
        $submission = $request->route('submission');
        require_login($submission->charon->course);
        $this->permissionsService->requireCourseManagementCapability($submission->charon->course);

        return $next($request);
    }
}
