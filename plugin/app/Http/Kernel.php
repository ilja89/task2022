<?php

namespace TTU\Charon\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use TTU\Charon\Http\Middleware\RequireSubmissionManaging;
use TTU\Charon\Http\Middleware\RequireSubmissionsView;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \TTU\Charon\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \TTU\Charon\Http\Middleware\LogDatabaseQuery::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \TTU\Charon\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \TTU\Charon\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \TTU\Charon\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,

        'auth.course_module.enrolment.require' => \TTU\Charon\Http\Middleware\RequireEnrolment::class,
        'auth.course.managing.require' => \TTU\Charon\Http\Middleware\RequireCourseManaging::class,
        'auth.charon.managing.require' => \TTU\Charon\Http\Middleware\RequireCharonManaging::class,
        'auth.submission.managing.require' => RequireSubmissionManaging::class,
        'auth.charon.submissions.view.require' => RequireSubmissionsView::class,
    ];
}
