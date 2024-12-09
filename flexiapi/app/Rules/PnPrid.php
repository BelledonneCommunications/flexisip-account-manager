<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Respect\Validation\Validator;

class PnPrid implements Rule
{
    public function passes($attribute, $value)
    {
        return $value == null || Validator::regex('/^[\w\-\:]+$/')->validate($value);
    }

    public function message()
    {
        return 'The :attribute should be null or contain only alphanumeric, dashes and colon characters';
    }
}
