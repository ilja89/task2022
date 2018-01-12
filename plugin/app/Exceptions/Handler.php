<?php

namespace TTU\Charon\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
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
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,

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
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception
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
            app('sneaker')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->expectsJson() && $exception instanceof CharonException) {
            return response()->json([
                'status' => $exception->getStatus(),
                'data' => $exception->toArray()
            ]);
        }

        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
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
    protected function shouldntSendEmail(\Exception $e)
    {
        foreach ($this->dontSendEmail as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }

        return false;
    }

    protected function shouldSendEmail(\Exception $e)
    {
        return ! $this->shouldntSendEmail($e);
    }

    private function isPrivateEnv()
    {
        return config('app.env') === 'local' || config('app.env') === 'testing';
    }
}
