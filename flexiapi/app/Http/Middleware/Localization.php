<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class Localization
{
    public function handle(Request $request, Closure $next)
    {
        $localization = $request->header('Accept-Language');
        $localization = in_array($localization, config('app.authorized_locales'), true)
            ? $localization
            : config('app.locale');

        App::setLocale($localization);

        return $next($request);
    }
}
