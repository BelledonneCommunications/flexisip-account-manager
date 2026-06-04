<?php

/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2020 Belledonne Communications SARL, All rights reserved.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace App\Http\Middleware;

use App\ApiKey;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Closure;

class AuthenticateKey
{
    public function handle(Request $request, Closure $next)
    {
        if (($request->server('SSL_CLIENT_CERT') || $request->bearerToken()) && Auth::check()) {
            return $next($request);
        }

        if ($request->header('x-api-key')) {
            $apiKey = ApiKey::with([
                'account' => function ($query) {
                    $query->withoutGlobalScopes();
                }
            ])->where('key', $request->header('x-api-key'))->first();

            if ($apiKey && ($apiKey->ip == null || $apiKey->ip == $request->ip())) {
                $apiKey->last_used_at = Carbon::now();
                $apiKey->requests = $apiKey->requests + 1;
                $apiKey->save();

                Auth::login($apiKey->account);
                $response = $next($request);

                return $response;
            }
            $response = new Response;
            $response->setStatusCode(401);
            $response->setContent('Invalid API Key');
            return $response;
        }

        return $next($request);
    }
}
