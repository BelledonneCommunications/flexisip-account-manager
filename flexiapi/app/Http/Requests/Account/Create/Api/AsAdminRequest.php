<?php

namespace App\Http\Requests\Account\Create\Api;

use App\Http\Requests\Account\Create\Request;
use App\Http\Requests\Api as RequestsApi;
use App\Http\Requests\AsAdmin;
use App\Rules\IsNotPhoneNumber;
use App\Rules\PasswordAlgorithm;

class AsAdminRequest extends Request
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
        $rules['confirmation_key_expires'] = [
            'date_format:Y-m-d H:i:s',
            'nullable',
        ];

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
