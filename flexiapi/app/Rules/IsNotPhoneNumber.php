<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Respect\Validation\Validator;
use Propaganistas\LaravelPhone\PhoneNumber;

class IsNotPhoneNumber implements Rule
{
    public function passes($attribute, $value): bool
    {
        return (new PhoneNumber($value))->getCountry() == null;
    }

    public function message()
    {
        return 'The :attribute should not be a phone number';
    }
}
