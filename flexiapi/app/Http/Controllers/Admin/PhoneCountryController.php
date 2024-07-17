<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\PhoneCountry;

use Illuminate\Http\Request;

class PhoneCountryController extends Controller
{
    public function index()
    {
        return view('admin.phone_country.index', [
            'phone_countries' => PhoneCountry::all()
        ]);
    }

    public function activateAll()
    {
        PhoneCountry::query()->update(['activated' => true]);

        return redirect()->route('admin.phone_countries.index');
    }

    public function deactivateAll()
    {
        PhoneCountry::query()->update(['activated' => false]);

        return redirect()->route('admin.phone_countries.index');
    }

    public function activate(string $code)
    {
        $phoneCountry = PhoneCountry::where('code', $code)->firstOrFail();

        PhoneCountry::where('country_code', $phoneCountry->country_code)->update(['activated' => true]);

        return redirect()->route('admin.phone_countries.index');
    }

    public function deactivate(string $code)
    {
        $phoneCountry = PhoneCountry::where('code', $code)->firstOrFail();

        PhoneCountry::where('country_code', $phoneCountry->country_code)->update(['activated' => false]);

        return redirect()->route('admin.phone_countries.index');
    }
}
