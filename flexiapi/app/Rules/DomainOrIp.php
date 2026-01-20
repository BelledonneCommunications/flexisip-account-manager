<?php

namespace App\Rules;

use App\Space;

use Illuminate\Contracts\Validation\Rule;
use Respect\Validation\Validator;

class DomainOrIp implements Rule
{
    public function passes($attribute, $value)
    {
        return Validator::regex('/' . Space::DOMAIN_REGEX . '/')->validate($value)
            || Validator::ip()->validate($value);
    }

    public function message()
    {
        return 'The :attribute should be a valid domain';
    }
}
