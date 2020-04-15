<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Account;
use App\Rules\SIP;
use App\Helpers\Utils;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        return view('account.index', [
            'account' => $request->user()
        ]);
    }

    public function login(Request $request)
    {
        return view('account.login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect()->route('account.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'username' => ['required', new SIP],
            'password' => 'required'
        ]);

        list($username, $domain) = explode('@', $request->get('username'));

        $account = Account::where('username', $username)
                          ->where('domain', $domain)
                          ->firstOrFail();

        // Try out the passwords
        foreach ($account->passwords as $password) {
            if (hash_equals(
                $password->password,
                Utils::bchash($username, $domain, $request->get('password'), $password->algorithm)
            )) {
                Auth::login($account);
                return redirect()->route('account.index');
            }
        }

        return redirect()->back();
    }
}
