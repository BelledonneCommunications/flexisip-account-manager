<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Account;
use App\Password;
use App\Helpers\Utils;

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
            'password' => 'required|confirmed|min:6',
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
                    return redirect()->route('account.panel');
                }
            }

            return redirect()->back()->withErrors(['old_password' => 'Old password not correct']);
        } else {
            // No password yet
            $this->updatePassword($account, $request->get('password'), $algorithm);

            return redirect()->back();
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
