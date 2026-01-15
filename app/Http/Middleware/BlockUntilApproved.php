<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class BlockUntilApproved
{
    public function handle($request, Closure $next)
{
    $user = Auth::user();

    // Skip check for waiting page
    if ($user && !$user->approved && !$request->routeIs('waitingapproval')) {
        return redirect()
            ->route('waitingapproval')
            ->with('error', 'Your account is still pending approval.');
    }

    return $next($request);
}

}
