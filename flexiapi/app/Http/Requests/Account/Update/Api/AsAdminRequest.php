<?php

namespace App\Http\Requests\Account\Update\Api;

use App\Http\Requests\Account\Update\Request as UpdateRequest;
use App\Http\Requests\Api as RequestsApi;
use App\Http\Requests\AsAdmin;
use App\Rules\IsNotPhoneNumber;
use App\Rules\PasswordAlgorithm;

class AsAdminRequest extends UpdateRequest
{
    use RequestsApi, AsAdmin;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = parent::rules();

        $rules['algorithm'] = ['required', new PasswordAlgorithm()];
        $rules['admin'] = 'boolean|nullable';
        $rules['activated'] = 'boolean|nullable';

        if (config('app.allow_phone_number_username_admin_api') == true) {
            array_splice(
                $rules['username'],
                array_search(new IsNotPhoneNumber(), $rules['username']),
                1
            );
        }

        return $rules;
    }
}
