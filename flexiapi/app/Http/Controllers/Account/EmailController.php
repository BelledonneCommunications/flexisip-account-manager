<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Mail\ChangedEmail;

class EmailController extends Controller
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
            'email' => 'required|unique:external.accounts,email|different:email_current|confirmed|email',
        ]);

        $account = $request->user();
        $account->email = $request->get('email');
        $account->save();

        Mail::to($account)->send(new ChangedEmail());

        return redirect()->route('account.panel');
    }
}
