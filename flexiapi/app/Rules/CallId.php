<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Respect\Validation\Validator;

class CallId implements Rule
{
    public function passes($attribute, $value)
    {
        return Validator::regex('/^[\w\-~]+$/')->validate($value);
    }

    public function message()
    {
        return 'The :attribute should only contain only alphanumeric, tilde and dashes characters';
    }
}
