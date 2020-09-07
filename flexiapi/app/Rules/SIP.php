<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class SIP implements Rule
{
    public function __construct()
    {
        //
    }

    public function passes($attribute, $value)
    {
        // TODO complete me
        return Str::contains($value, '@');
    }

    public function message()
    {
        return 'The :attribute must be a SIP address.';
    }
}
