<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    protected function handleApiException($request, Throwable $exception)
    {
        $status = 500;
        $message = 'Internal Server Error';
        $errors = null;

        if ($exception instanceof ValidationException) {
            $status = 422;
            $message = 'Validation Error';
            $errors = $exception->errors();
        } elseif ($exception instanceof ModelNotFoundException) {
            $status = 404;
            $message = 'Resource not found';
        } elseif ($exception instanceof NotFoundHttpException) {
            $status = 404;
            $message = 'Endpoint not found';
        } elseif ($exception instanceof AuthenticationException) {
            $status = 401;
            $message = 'Unauthenticated';
        } elseif (config('app.debug')) {
            $message = $exception->getMessage();
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => null,
            'errors' => $errors
        ], $status);
    }
}
