<?php

namespace App\Http\Requests\Account\Create\Web;

use App\Http\Requests\Account\Create\Request as CreateRequest;

class Request extends CreateRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = parent::rules();

        $rules['h-captcha-response'] = captchaConfigured() ? 'required|HCaptcha' : '';
        $rules['password'] = 'confirmed';
        $rules['email'] = 'confirmed';
        $rules['terms'] = 'accepted';

        return $rules;
    }
}
