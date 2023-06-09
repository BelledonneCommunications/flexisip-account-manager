<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Account;
use App\Rules\BlacklistedUsername;
use App\Rules\IsNotPhoneNumber;
use App\Rules\NoUppercase;
use App\Rules\SIPUsername;
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
                new BlacklistedUsername,
                new SIPUsername,
                Rule::unique('accounts', 'username')->where(function ($query) {
                    $query->where('domain', resolveDomain($this));
                }),
                /*Rule::unique('accounts_tombstones', 'username')->where(function ($query) use ($request) {
                    $query->where('domain', config('app.sip_domain'));
                }),*/
                'filled',
            ],
            'password' => 'required|min:3',
            'email' => config('app.account_email_unique')
                ? 'nullable|email|unique:accounts,email'
                : 'nullable|email',
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
