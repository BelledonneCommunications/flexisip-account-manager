<?php

namespace App\Rules;

use App\Account;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SipUriFromTheSpace implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !isSip($value)) {
            $fail('This field must be a valid SIP URI.')->translate();
        }

        if (!Account::sip($value)->exists()) {
            $fail('This SIP URI must belong to an existing account of the domain.')->translate();
        }
    }
}
