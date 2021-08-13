<?php

namespace TTU\Charon\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     * Logged.
     *
     * @var array
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        TokenMismatchException::class,
        ValidationException::class,

        ResultPointsRequiredException::class,
        SubmissionNoGitCallbackException::class,
    ];

    /**
     * A list of the exception types that should not be reported via email.
     *
     * @var array
     */
    protected $dontSendEmail = [
         // CharonNotFoundException::class,
        NotFoundHttpException::class,  // When route cannot be matched
        ResultPointsRequiredException::class,  // When no calculated result set while updating results
        SubmissionNoGitCallbackException::class,
        \require_login_exception::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param Exception $exception
     *
     * @return void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        if (\App::environment('testing')) {
            throw $exception;
        }

        if (
            $this->shouldReport($exception) &&
            $this->shouldSendEmail($exception) &&
            ! $this->isPrivateEnv()
        ) {
            // Don't try to email exceptions when in local environment.
            //app('sneaker')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Exception $exception
     *
     * @return Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->expectsJson()) {
            if ($exception instanceof RegistrationException) {
                return response()->json($exception->toArray(), $exception->getStatus());
            }

            if ($exception instanceof CharonException) {
                return response()->json([
                    'status' => $exception->getStatus(),
                    'data' => $exception->toArray()
                ]);
            }

            if ($exception instanceof ModelNotFoundException) {
                return response()->json([
                    'status' => 404,
                    'data' => [
                        'message' => $exception->getMessage(),
                    ],
                ], 404);
            }
        }

        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  Request  $request
     * @param AuthenticationException $exception
     * @return Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }

    /**
     * Checks if the given exception should not be reported via email.
     *
     * @param  Exception  $e
     *
     * @return bool
     */
    protected function shouldntSendEmail(Exception $e)
    {
        foreach ($this->dontSendEmail as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }

        return false;
    }

    protected function shouldSendEmail(Exception $e)
    {
        return ! $this->shouldntSendEmail($e);
    }

    private function isPrivateEnv()
    {
        return config('app.env') === 'local' || config('app.env') === 'testing';
    }
}
