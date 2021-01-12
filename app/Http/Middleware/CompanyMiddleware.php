<?php

namespace Crater\Http\Middleware;

use Auth;
use Closure;

class CompanyMiddleware
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
        if ( $request->header('company') && $request->header('company') != Auth::user()->company_id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Company not allowed.', 401);
            } else {
                return response()->json(['error' => 'company_not_allowed'], 404);
            }
        }

        return $next($request);
    }
}
