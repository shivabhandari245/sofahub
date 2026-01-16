<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventAdminActionsWhileImpersonating
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle($request, Closure $next)
{
    if (
        session()->has('impersonator_id') &&
        $request->is('admin/*')
    ) {
        abort(403, 'Exit impersonation first');
    }

    return $next($request);
}

}
