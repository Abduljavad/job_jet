<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    public function register(): void
    {
        $this->renderable(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/auth/*')) {
                return response()->json([
                    'message' => 'invalid ability provided',
                ], 403);
            }
        });

        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            return response()->json([
                'message' => 'Model Not Found',
            ], 403);
        });
    }
}
