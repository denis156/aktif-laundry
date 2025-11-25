<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureKurirEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get authenticated kurir using guard 'kurir'
        $kurir = Auth::guard('kurir')->user();

        // If kurir is not authenticated, let auth middleware handle it
        if (! $kurir) {
            return redirect()->route('login.kurir');
        }

        // Check if kurir implements MustVerifyEmail and has not verified email
        if ($kurir instanceof MustVerifyEmail && ! $kurir->hasVerifiedEmail()) {
            return redirect()->route('kurir.verification.notice');
        }

        return $next($request);
    }
}
