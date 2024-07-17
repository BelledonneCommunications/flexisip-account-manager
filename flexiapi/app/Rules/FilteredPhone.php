<?php

namespace App\Rules;

use App\PhoneCountry;
use Illuminate\Contracts\Validation\Rule;
use Propaganistas\LaravelPhone\PhoneNumber;

class FilteredPhone implements Rule
{
    public function passes($attribute, $value)
    {
        if (!PhoneCountry::where('code', (new PhoneNumber($value))->getCountry())
            ->where('activated', true)
            ->exists()) return false;

        return true;
    }

    public function message()
    {
        return 'The phone number must belong to an authorized country.';
    }
}
