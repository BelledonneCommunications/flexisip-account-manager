<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class Localization
{
    public function handle(Request $request, Closure $next)
    {
        App::setLocale($request->getPreferredLanguage(config('app.authorized_locales')));

        return $next($request);
    }
}
