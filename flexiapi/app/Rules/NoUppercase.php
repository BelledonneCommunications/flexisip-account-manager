<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NoUppercase implements Rule
{
    public function passes($attribute, $value)
    {
        return strtolower($value) == $value;
    }

    public function message()
    {
        return 'No uppercase letters are allowed.';
    }
}
