<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // Cek apakah request ke route dengan prefix /kurir
            if ($request->is('kurir') || $request->is('kurir/*')) {
                return route('login.kurir');
            }

            // Default redirect ke login management
            return route('login');
        }

        return null;
    }
}
