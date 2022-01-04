<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;
use App\Rules\WithoutSpaces;

class CreateAccountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'username' => [
                'required',
                Rule::unique('accounts', 'username')->where(function ($query) {
                    $query->where('domain', config('app.sip_domain'));
                }),
                'filled',
            ],
            'domain' => config('app.admins_manage_multi_domains') ? 'required' : '',
            'password' => 'required|min:3',
            'email' => 'nullable|email',
            'phone' => [
                'nullable',
                'unique:aliases,alias',
                'unique:accounts,username',
                new WithoutSpaces, 'starts_with:+'
            ]
        ];
    }
}
