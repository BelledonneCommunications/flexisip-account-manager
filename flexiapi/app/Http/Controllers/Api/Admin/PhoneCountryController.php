<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\PhoneCountry;

class PhoneCountryController extends Controller
{
    public function activate(string $code)
    {
        $phoneCountry = PhoneCountry::where('code', $code)->firstOrFail();
        return PhoneCountry::where('country_code', $phoneCountry->country_code)->update(['activated' => true]);
    }

    public function deactivate(string $code)
    {
        $phoneCountry = PhoneCountry::where('code', $code)->firstOrFail();
        return PhoneCountry::where('country_code', $phoneCountry->country_code)->update(['activated' => false]);
    }
}
