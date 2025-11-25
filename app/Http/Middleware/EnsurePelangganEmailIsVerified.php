<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePelangganEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get authenticated pelanggan using guard 'pelanggan'
        $pelanggan = Auth::guard('pelanggan')->user();

        // If pelanggan is not authenticated, let auth middleware handle it
        if (! $pelanggan) {
            return redirect()->route('login.pelanggan');
        }

        // Check if pelanggan implements MustVerifyEmail and has not verified email
        if ($pelanggan instanceof MustVerifyEmail && ! $pelanggan->hasVerifiedEmail()) {
            return redirect()->route('pelanggan.verification.notice');
        }

        return $next($request);
    }
}
