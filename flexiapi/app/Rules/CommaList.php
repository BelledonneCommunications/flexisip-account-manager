<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Respect\Validation\Validator;

class CommaList implements Rule
{
    public function passes($attribute, $value)
    {
        preg_match_all('/[^, ]+/', $value, $matches);

        return $value == null || (!empty($matches) && (implode(',', $matches[0]) == $value));
    }

    public function message()
    {
        return 'The :attribute should be null or contain a list of words separated by commas without spaces';
    }
}
