<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountEmailController extends Controller
{
    public function show(Request $request)
    {
        return view('account.email', [
            'account' => $request->user()
        ]);
    }

    public function update(Request $request)
    {
        // TODO exists doesn't already exists
        $request->validate([
            'email' => 'required|email',
            'email_confirm' => 'required|same:email'
        ]);

        $account = $request->user();
        $account->email = $request->get('email');
        $account->save();

        return redirect()->route('account.index');
    }
}
