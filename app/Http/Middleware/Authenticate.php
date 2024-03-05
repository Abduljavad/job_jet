<?php

namespace App\Http\Middleware;

use App\Http\Traits\ResponseTraits;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    use ResponseTraits;

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : $this->errorResponse('Not Found', 404);
    }
}
