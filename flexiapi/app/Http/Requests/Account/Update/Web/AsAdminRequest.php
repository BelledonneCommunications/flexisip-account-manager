<?php

namespace App\Http\Requests\Account\Update\Web;

use App\Http\Requests\Account\Update\Request as UpdateRequest;
use App\Http\Requests\AsAdmin;
use App\Rules\IsNotPhoneNumber;

class AsAdminRequest extends UpdateRequest
{
    use AsAdmin;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = parent::rules();

        $rules['password'] = 'confirmed';

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
