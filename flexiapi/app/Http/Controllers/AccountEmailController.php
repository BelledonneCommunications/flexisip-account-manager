<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Mail\ChangedEmail;

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
        $request->validate([
            'email' => 'required|confirmed|email',
        ]);

        $account = $request->user();
        $account->email = $request->get('email');
        $account->save();

        Mail::to($account)->send(new ChangedEmail());

        return redirect()->route('account.index');
    }
}
