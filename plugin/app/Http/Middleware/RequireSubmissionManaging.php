<?php

namespace TTU\Charon\Http\Middleware;

use Closure;
use TTU\Charon\Exceptions\CourseManagementPermissionException;
use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Globals\User;
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
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     * @throws CourseManagementPermissionException
     */
    public function handle($request, Closure $next)
    {
        if (config('app.env') === 'testing') {
            return $next($request);
        }

        /** @var Submission $submission */
        $submission = $request->route('submission');

        $courseId = $submission->charon->course;
        require_login($courseId);

        if ($submission->user_id == app(User::class)->currentUserId()) {
            return $next($request);
        }

        try {
            $this->permissionsService->requireCourseManagementCapability($courseId);
        } catch (\required_capability_exception $e) {
            throw new CourseManagementPermissionException(
                'course_management_permission_denied',
                app(User::class)->currentUserId(),
                $request->getClientIp(),
                $submission->charon->course
            );
        }

        return $next($request);
    }
}
