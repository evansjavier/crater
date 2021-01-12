<?php

namespace Crater\Http\Middleware;

use Auth;
use Closure;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest() || !Auth::user()->isSuperAdmin()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized. No Super Admin', 401);
            } else {
                return response()->json(['error' => 'user_is_not_super_admin'], 404);
            }
        }

        return $next($request);
    }
}
