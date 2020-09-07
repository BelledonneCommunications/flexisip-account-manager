<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Account;
use App\Password;
use App\Helpers\Utils;
use App\Mail\ConfirmedRegistration;

class PasswordController extends Controller
{
    public function show(Request $request)
    {
        return view('account.password', [
            'account' => $request->user()
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|filled',
        ]);

        $account = $request->user();
        $account->activated = true;
        $account->save();

        $algorithm = $request->has('password_sha256') ? 'SHA-256' : 'MD5';

        if ($account->passwords()->count() > 0) {
            $request->validate(['old_password' => 'required']);

            foreach ($account->passwords as $password) {
                // If one of the password stored equals the one entered
                if (hash_equals(
                    $password->password,
                    Utils::bchash($account->username, $account->domain, $request->get('old_password'), $password->algorithm)
                )) {
                    $this->updatePassword($account, $request->get('password'), $algorithm);

                    $request->session()->flash('success', 'Password successfully changed');
                    return redirect()->route('account.panel');
                }
            }

            return redirect()->back()->withErrors(['old_password' => 'Old password not correct']);
        } else {
            // No password yet
            $this->updatePassword($account, $request->get('password'), $algorithm);

            if (!empty($account->email)) {
                Mail::to($account)->send(new ConfirmedRegistration($account));
            }

            $request->session()->flash('success', 'Password successfully set. Your SIP account creation process is now finished.');

            return redirect()->route('account.panel');
        }
    }

    private function updatePassword(Account $account, $newPassword, $algorithm)
    {
        $account->passwords()->delete();

        $password = new Password;
        $password->account_id = $account->id;
        $password->password = Utils::bchash($account->username, $account->domain, $newPassword, $algorithm);
        $password->algorithm = $algorithm;
        $password->save();
    }
}
