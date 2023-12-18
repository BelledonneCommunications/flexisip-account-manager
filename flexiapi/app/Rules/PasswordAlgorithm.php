<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PasswordAlgorithm implements Rule
{
    public function passes($attribute, $value)
    {
        return in_array($value, array_keys(passwordAlgorithms()));
    }

    public function message()
    {
        return 'The password algorithm must be in ' . implode(', ', array_keys(passwordAlgorithms()));
    }
}
