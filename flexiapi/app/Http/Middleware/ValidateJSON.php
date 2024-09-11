<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateJSON
{
    public static $message = 'Invalid JSON';

    public function handle(Request $request, Closure $next)
    {
        if ($request->expectsJson()) {
            json_decode($request->getContent());
            if (json_last_error() !== JSON_ERROR_NONE) {
                abort(400, self::$message . ': ' . json_last_error_msg());
            }
        }

        return $next($request);
    }
}
