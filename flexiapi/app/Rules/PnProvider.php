<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Respect\Validation\Validator;

class PnProvider implements Rule
{
    private $values = ['apns.dev', 'apns', 'fcm'];

    public function passes($attribute, $value)
    {
        return in_array($value, $this->values);
    }

    public function message()
    {
        return 'The :attribute should be in ' . implode(', ', $this->values);
    }
}
