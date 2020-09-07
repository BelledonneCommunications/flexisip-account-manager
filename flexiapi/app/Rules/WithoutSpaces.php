<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class WithoutSpaces implements Rule
{
    public function __construct()
    {
        //
    }

    public function passes($attribute, $value)
    {
        return preg_match('/^\S*$/u', $value);
    }

    public function message()
    {
        return 'The :attribute contains spaces';
    }
}
