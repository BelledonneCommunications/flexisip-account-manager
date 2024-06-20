<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Dictionary implements Rule
{
    public function passes($attribute, $array): bool
    {
        if (!is_array($array)) return false;

        foreach ($array as $key => $value) {
            if (!is_string($key) || !is_string($value)) return false;
        }

        return true;
    }

    public function message()
    {
        return 'The dictionary must be an associative dictionary of strings';
    }
}
