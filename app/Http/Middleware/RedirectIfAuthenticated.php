<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  ...$guards
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Redirect based on guard
                if ($guard === 'kurir') {
                    return redirect()->route('beranda.kurir');
                }

                if ($guard === 'pelanggan') {
                    return redirect()->route('beranda.pelanggan');
                }

                // Default redirect untuk guard web (management)
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
