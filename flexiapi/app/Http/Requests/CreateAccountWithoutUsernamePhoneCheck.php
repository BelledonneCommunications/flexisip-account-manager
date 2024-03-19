<?php

namespace App\Http\Requests;

use App\Rules\IsNotPhoneNumber;

class CreateAccountWithoutUsernamePhoneCheck extends CreateAccountRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $parentRules = parent::rules();

        if (config('app.allow_phone_number_username_admin_api') == true) {
            array_splice(
                $parentRules['username'],
                array_search(new IsNotPhoneNumber(), $parentRules['username']),
                1
            );
        }

        return $parentRules;
    }
}
