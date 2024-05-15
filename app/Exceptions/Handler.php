<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ModelNotFoundException) {
            return response()->json(['saved' => false, 'errorCode' => 404, 'content' => 'Model Not Found Exception']);
        }
        if ($exception instanceof NotFoundHttpException) {
            return response()->json(['saved' => false, 'errorCode' => 404, 'content' => 'Route Not Found Exception']);
        }
        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json(['saved' => false, 'errorCode' => 405, 'content' => 'Method Not Allowed Http Exception']);
        }
        /*  if ($exception instanceof \ErrorException) {
              return response()->json(['saved' => false, 'errorCode' => 500, 'content' => '505 Internal Server Error'], 500);
          }*/
        return parent::render($request, $exception);
    }
}
