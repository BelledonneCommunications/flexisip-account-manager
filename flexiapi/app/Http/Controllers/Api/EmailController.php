<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmailController extends Controller
{
    public function requestUpdate(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', Rule::notIn([$request->user()->email])],
        ]);
        $request->user()->requestEmailUpdate($request->get('email'));
    }
}
