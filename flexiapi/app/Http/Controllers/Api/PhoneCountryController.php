<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\PhoneCountry;

class PhoneCountryController extends Controller
{
    public function index()
    {
        return PhoneCountry::all();
    }
}
