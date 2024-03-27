<?php

namespace App\Http\Requests\Account\Create\Api;

use App\Http\Requests\Account\Create\Request as CreateRequest;
use App\Http\Requests\Api as RequestsApi;
use App\Rules\AccountCreationToken;

class Request extends CreateRequest
{
    use RequestsApi;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules['account_creation_token'] = ['required', new AccountCreationToken()];

        return $rules;
    }
}
