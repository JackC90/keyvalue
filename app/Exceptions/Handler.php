<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                $status = 500;
                $response = [
                    'message' => 'Server Error',
                    'status' => 500
                ];

                if ($e instanceof ValidationException) {
                    $status = 422;
                    $response = [
                        'message' => 'The given data was invalid.',
                        'errors' => $e->validator->errors()->toArray(),
                        'status' => 422
                    ];
                }

                if ($e instanceof ModelNotFoundException) {
                    $status = 404;
                    $response = [
                        'message' => 'Resource not found',
                        'status' => 404
                    ];
                }

                if ($e instanceof NotFoundHttpException) {
                    $status = 404;
                    $response = [
                        'message' => 'The requested URL was not found',
                        'status' => 404
                    ];
                }

                if ($e instanceof MethodNotAllowedHttpException) {
                    $status = 405;
                    $response = [
                        'message' => 'Method not allowed',
                        'status' => 405
                    ];
                }

                // Add debug information if APP_DEBUG is true
                if (config('app.debug')) {
                    $response['debug'] = [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTrace()
                    ];
                }

                return response()->json($response, $status);
            }

            return parent::render($request, $e);
        });
    }
} 