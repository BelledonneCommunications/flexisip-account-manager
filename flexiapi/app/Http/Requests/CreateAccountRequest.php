<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Account;
use App\Rules\IsNotPhoneNumber;
use App\Rules\NoUppercase;
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
                new NoUppercase,
                new IsNotPhoneNumber,
                Rule::unique('accounts', 'username')->where(function ($query) {
                    $query->where('domain', config('app.sip_domain'));
                }),
                'filled',
            ],
            'domain' => config('app.admins_manage_multi_domains') ? 'required' : '',
            'password' => 'required|min:3',
            'email' => 'nullable|email',
            'dtmf_protocol' => 'nullable|in:' . Account::dtmfProtocolsRule(),
            'phone' => [
                'nullable',
                'unique:aliases,alias',
                'unique:accounts,username',
                new WithoutSpaces, 'starts_with:+'
            ]
        ];
    }
}
