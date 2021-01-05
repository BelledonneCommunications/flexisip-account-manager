<?php

namespace App\Http\Middleware;

use Closure;

class AuthenticateAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->user()) {
            return redirect()->route('account.login');
        }

        if (!$request->user()->isAdmin() && !config('app.everyone_is_admin')) {
            return abort(403, 'Unauthorized area');
        }

        return $next($request);
    }
}
