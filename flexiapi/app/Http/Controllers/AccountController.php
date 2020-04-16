<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use App\Account;
use App\Rules\SIP;
use App\Helpers\Utils;

class AccountController extends Controller
{
    private $emailCodeSize = 14;

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

    public function loginEmail(Request $request)
    {
        return view('account.login_email');
    }

    public function authenticateEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:external.accounts,email']);

        $account = Account::where('email', $request->get('email'))->first();
        $account->confirmation_key = Str::random($this->emailCodeSize);
        $account->save();

        // TODO send email

        return view('account.authenticate_email', [
            'account' => $account
        ]);
    }

    public function authenticateEmailConfirm(Request $request, string $code)
    {
        $request->merge(['code' => $code]);
        $request->validate(['code' => 'required|size:'.$this->emailCodeSize]);

        $account = Account::where('confirmation_key', $code)->firstOrFail();
        $account->confirmation_key = null;
        $account->save();

        Auth::login($account);
        return redirect()->route('account.index');
    }

    public function loginPhone(Request $request)
    {
        return view('account.login_phone');
    }

    public function authenticatePhone(Request $request)
    {
        $request->validate(['phone' => 'required|starts_with:+|phone:AUTO']);

        $account = Account::where('username', $request->get('phone'))->first();

        // TODO add alias

        if (!$account) {
            return view('account.login_phone')->withErrors([
                'phone' => 'Phone number not found'
            ]);
        }

        $account->confirmation_key = mt_rand(1000, 9999);
        $account->save();

        // TODO send SMS

        return view('account.authenticate_phone', [
            'account' => $account
        ]);
    }

    public function authenticatePhoneConfirm(Request $request)
    {
        $request->validate([
            'account_id' => 'required',
            'code' => 'required|digits:4'
        ]);

        $account = Account::where('id', $request->get('account_id'))->firstOrFail();

        if ($account->confirmation_key != $request->get('code')) {
            return view('account.login_phone')->withErrors([
                'code' => 'Wrong code'
            ]);
        }

        $account->confirmation_key = null;
        $account->save();

        Auth::login($account);
        return redirect()->route('account.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect()->route('account.login');
    }
}
