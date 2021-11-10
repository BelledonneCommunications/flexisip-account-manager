<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;
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
                Rule::unique('accounts', 'username')->where(function ($query) {
                    $query->where('domain', config('app.sip_domain'));
                })->ignore($this->route('id'), 'id'),
                'filled',
            ],
            'email' => 'nullable|email',
            'password_sha256' => 'nullable|min:3',
            'phone' => [
                'nullable',
                Rule::unique('accounts', 'username')->where(function ($query) {
                    $query->where('domain', config('app.sip_domain'));
                })->ignore($this->route('id'), 'id'),
                Rule::unique('aliases', 'alias')->ignore($this->route('id'), 'account_id'),
                new WithoutSpaces, 'starts_with:+'
            ]
        ];
    }
}