<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidCode implements Rule
{
    public function passes($attribute, $value): bool
    {
        return false;
    }

    public function message()
    {
        return __('The :attribute should be valid');
    }
}
