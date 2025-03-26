<?php

namespace App\Rules;

use App\Space;

use Illuminate\Contracts\Validation\Rule;
use Respect\Validation\Validator;

class Domain implements Rule
{
    public function passes($attribute, $value)
    {
        return Validator::regex('/' . Space::DOMAIN_REGEX . '/')->validate($value);
    }

    public function message()
    {
        return 'The :attribute should be a valid domain';
    }
}
