<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;


class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    // public function register(): void
    // {
    //     $this->reportable(function (Throwable $e) {
    //         //
    //     });
    // }


    public function register()
    {
        // return $this->reportable(function (Throwable $e) {
        // });

        $this->renderable(function (Throwable $exception, $request) {
            if ($exception instanceof Exception) {
                if ($exception->getMessage() == 'Route [login] not defined222.') {
                    return response()->json(
                        [
                            'message' => $exception->getMessage(),
                        ],
                        402
                    );
                } elseif ($exception->getMessage() == "غير مصرح لك بالدخول") {
                    return response()->json(
                        [
                            'message' => $exception->getMessage(),
                        ],
                        403
                    );
                }
            }
            if ($exception instanceof AuthenticationException) {

                return response()->json(
                    [
                        'message' => $exception->getMessage(),
                    ],
                    402
                );
            }
        });
    }
}
