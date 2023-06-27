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

class UpdateAccountRequest extends FormRequest
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
                })->ignore($this->route('account_id'), 'id'),
                'filled',
            ],
            'email' => [
                'nullable',
                'email',
                config('app.account_email_unique') ? Rule::unique('accounts', 'email')->ignore($this->route('account_id')) : null
            ],
            'role' => 'in:admin,end_user',
            'password_sha256' => 'nullable|min:3',
            'dtmf_protocol' => 'nullable|in:' . Account::dtmfProtocolsRule(),
            'phone' => [
                'nullable',
                Rule::unique('accounts', 'username')->where(function ($query) {
                    $query->where('domain', config('app.sip_domain'));
                })->ignore($this->route('account_id'), 'id'),
                Rule::unique('aliases', 'alias')->ignore($this->route('account_id'), 'account_id'),
                new WithoutSpaces, 'starts_with:+'
            ]
        ];
    }
}
