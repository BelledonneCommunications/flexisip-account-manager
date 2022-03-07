<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Respect\Validation\Validator;

class IsNotPhoneNumber implements Rule
{
    public function passes($attribute, $value): bool
    {
        return (Validator::not(Validator::phone())->not(Validator::startsWith('+'))->noWhitespace()->validate($value));
    }

    public function message()
    {
        return 'The :attribute should not be a phone number';
    }
}
