<?php

namespace App\Http\Controllers\Api;

use App\AuthToken;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthTokenController extends Controller
{
    public function store()
    {
        $authToken = new AuthToken;
        $authToken->token = Str::random(32);
        $authToken->save();

        return $authToken;
    }

    public function attach(Request $request, string $token)
    {
        $authToken = AuthToken::where('token', $token)->valid()->firstOrFail();

        if (!$authToken->account_id) {
            $authToken->account_id = $request->user()->id;
            $authToken->save();

            return;
        }

        abort(404);
    }
}
