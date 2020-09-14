<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use App\Http\Controllers\Controller;
use App\Mail\ChangingEmail;
use App\Mail\ChangedEmail;
use App\EmailChanged;

class EmailController extends Controller
{
    public function show(Request $request)
    {
        return view('account.email', [
            'account' => $request->user()
        ]);
    }

    public function requestUpdate(Request $request)
    {
        $request->validate([
            'email' => 'required|different:email_current|confirmed|email',
        ]);

        // Remove all the old requests
        EmailChanged::where('account_id', $request->user()->id)->delete();

        // Create a new one
        $emailChanged = new EmailChanged;
        $emailChanged->new_email = $request->get('email');
        $emailChanged->hash = Str::random(16);
        $emailChanged->account_id = $request->user()->id;
        $emailChanged->save();

        $request->user()->refresh();

        Mail::to($request->user())->send(new ChangingEmail($request->user()));

        $request->session()->flash('success', 'An email was sent with a confirmation link. Please click it to update your email address.');
        return redirect()->route('account.panel');
    }

    public function update(Request $request, string $hash)
    {
        $account = $request->user();

        if ($account->emailChanged && $account->emailChanged->hash == $hash) {
            $account->email = $account->emailChanged->new_email;
            $account->save();

            Mail::to($account)->send(new ChangedEmail());

            $account->emailChanged->delete();

            $request->session()->flash('success', 'Email successfully updated');
            return redirect()->route('account.panel');
        }

        abort(404);
    }
}
