<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SipUri implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !isSip($value)) {
            $fail('This field must be a valid SIP URI.')->translate();
        }
    }
}
