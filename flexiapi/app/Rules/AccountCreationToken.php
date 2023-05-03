<?php

namespace App\Rules;

use App\AccountCreationToken as AppAccountCreationToken;
use App\Http\Controllers\Account\AuthenticateController;
use Illuminate\Contracts\Validation\Rule;

class AccountCreationToken implements Rule
{
    public function passes($attribute, $value)
    {
        return AppAccountCreationToken::where('token', $value)->where('used', false)->exists()
            && strlen($value) == AuthenticateController::$emailCodeSize;
    }

    public function message()
    {
        return 'Please provide a valid account_creation_token';
    }
}
