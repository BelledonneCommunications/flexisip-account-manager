<?php

namespace App\Rules;

use Sabre\VObject;
use Illuminate\Contracts\Validation\Rule;

class Vcard implements Rule
{
    private $message = null;

    public function __construct()
    {
        //
    }

    public function passes($attribute, $value): bool
    {
        try {
            $vcard = VObject\Reader::read($value);

            if (!empty($vcard->validate())) return false;
            if ($vcard->UID == null) return false;

            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function message()
    {
        return 'Invalid vcard passed';
    }
}
