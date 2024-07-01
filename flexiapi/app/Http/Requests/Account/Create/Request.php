<?php

namespace App\Http\Requests\Account\Create;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Account;
use App\Rules\BlacklistedUsername;
use App\Rules\Dictionary;
use App\Rules\IsNotPhoneNumber;
use App\Rules\NoUppercase;
use App\Rules\SIPUsername;
use App\Rules\WithoutSpaces;

class Request extends FormRequest
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
                new NoUppercase(),
                new IsNotPhoneNumber(),
                new BlacklistedUsername(),
                new SIPUsername(),
                Rule::unique('accounts', 'username')->where(function ($query) {
                    $query->where('domain', resolveDomain($this));
                }),
                Rule::unique('accounts_tombstones', 'username')->where(function ($query) {
                    $query->where('domain', resolveDomain($this));
                }),
                'filled',
            ],
            'domain' => 'exists:sip_domains,domain',
            'dictionary' => [new Dictionary()],
            'password' => 'required|min:3',
            'email' => config('app.account_email_unique')
                ? 'nullable|email|unique:accounts,email'
                : 'nullable|email',
            'dtmf_protocol' => 'nullable|in:' . Account::dtmfProtocolsRule(),
            'phone' => [
                'nullable',
                'unique:accounts,phone',
                'unique:accounts,username',
                new WithoutSpaces(), 'starts_with:+'
            ]
        ];
    }
}
