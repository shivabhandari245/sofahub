<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class BlockUntilApproved
{
   
     public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // If not logged in, allow guest routes
        if (!$user) {
            return $next($request);
        }

        // Block unapproved users (except waiting page)
        if (!$user->approved && !$request->routeIs('waitingapproval')) {
            return redirect()->route('waitingapproval')
                ->with('error', 'Your account is still pending approval.');
        }

        // Block users with unverified OTP (except OTP routes)
        if (!$user->otp_verified && 
            !$request->routeIs('otp.index') &&
            !$request->routeIs('otp.verify') &&
            !$request->routeIs('otp.resend'))
        {
            return redirect()->route('otp.index')
                ->with('message', 'Please verify your OTP to continue.');
        }

        return $next($request);
    }

}
