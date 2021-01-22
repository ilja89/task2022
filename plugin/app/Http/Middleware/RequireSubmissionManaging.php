<?php

namespace TTU\Charon\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use TTU\Charon\Exceptions\CourseManagementPermissionException;
use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Globals\User;
use Zeizig\Moodle\Services\PermissionsService;

class RequireSubmissionManaging
{
    /** @var PermissionsService */
    private $permissionsService;

    /** @var User */
    private $user;

    /**
     * RequireSubmissionManaging constructor.
     *
     * @param PermissionsService $permissionsService
     * @param User $user
     */
    public function __construct(PermissionsService $permissionsService, User $user)
    {
        $this->permissionsService = $permissionsService;
        $this->user = $user;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
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

        $userId = $this->user->currentUserId();

        if ($submission->users->pluck('id')->contains($userId)) {
            return $next($request);
        }

        try {
            $this->permissionsService->requireCourseManagementCapability($courseId);
        } catch (\required_capability_exception $e) {
            throw new CourseManagementPermissionException(
                'course_management_permission_denied',
                $userId,
                $request->getClientIp(),
                $submission->charon->course
            );
        }

        return $next($request);
    }
}
