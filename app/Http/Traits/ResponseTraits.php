<?php

namespace App\Http\Traits;

trait ResponseTraits
{
    public function successResponse($message = 'success', $statusCode = 200)
    {
        return response()->json(
            [
                'message' => $message,
                'statusCode' => $statusCode,
            ], $statusCode
        );
    }

    public function errorResponse($error = 'Error', $statusCode = 400)
    {
        return response()->json([
            'error' => $error,
            'statusCode' => $statusCode,
        ], $statusCode);
    }
}
