<?php

namespace App\Http\Controllers\Api\Account;

use App\AccountCreationRequestToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Account\AuthenticateController as WebAuthenticateController;
use App\Http\Controllers\Controller;

class CreationRequestToken extends Controller
{
    public function create(Request $request)
    {
        $creationRequestToken = new AccountCreationRequestToken;
        $creationRequestToken->token = Str::random(WebAuthenticateController::$emailCodeSize);
        $creationRequestToken->save();

        return $creationRequestToken;
    }
}
