<?php

namespace App\Http\Controllers\Api;

use App\AuthToken;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class ApiKeyController extends Controller
{
    public function generate(Request $request)
    {
        $account = $request->user();
        $account->generateApiKey();

        $account->refresh();
        Cookie::queue('x-api-key', $account->apiKey->key, config('app.api_key_expiration_minutes'));

        return $account->apiKey->key;
    }

    public function generateFromToken(string $token)
    {
        $authToken = AuthToken::where('token', $token)->valid()->firstOrFail();

        if ($authToken->account) {
            $authToken->account->generateApiKey();

            $authToken->account->refresh();
            Cookie::queue('x-api-key', $authToken->account->apiKey->key, config('app.api_key_expiration_minutes'));

            $apiKey = $authToken->account->apiKey->key;
            $authToken->delete();

            return response()->json(['api_key' => $apiKey]);
        }

        abort(404);
    }
}
