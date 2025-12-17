<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Filament\Facades\Filament;

class EnsureTwoFactorVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only run if user is logged in
        if (! auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // If user has 2FA enabled
        if ($user->otp_enabled) {
            // Check if session has verification flag
            if (! session()->has('2fa_verified')) {
                // If the current route is NOT the verification route, redirect
                if (! $request->routeIs('filament.admin.pages.auth.verify-two-factor')) {
                    // Make sure we don't redirect if we are logging out
                    if ($request->routeIs('filament.admin.auth.logout')) {
                        return $next($request); 
                    }
                    
                    return redirect()->route('filament.admin.pages.auth.verify-two-factor');
                }
            }
        }

        return $next($request);
    }
}
