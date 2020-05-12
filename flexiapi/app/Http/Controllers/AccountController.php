<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

use App\Account;
use App\Alias;
use App\Configuration;
use App\Rules\SIP;
use App\Helpers\Utils;
use App\Libraries\OvhSMS;
use App\Mail\PasswordAuthentication;
use App\Mail\RegisterConfirmation;

class AccountController extends Controller
{
    private $emailCodeSize = 14;

    public function index(Request $request)
    {
        return view('account.index', [
            'account' => $request->user()
        ]);
    }

    public function terms(Request $request)
    {
        return view('account.terms');
    }

    public function login(Request $request)
    {
        return view('account.login');
    }

    public function register(Request $request)
    {
        return view('account.register', [
            'domain' => '@' . config('app.sip_domain'),
            'configuration' => Configuration::first()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'terms' =>'accepted',
            'username' => 'required|unique:external.accounts,username|min:6',
            'phone' => 'required_without:email|nullable|unique:external.aliases,alias|unique:external.accounts,username|starts_with:+|phone:AUTO',
            'g-recaptcha-response'  => 'required|captcha',
            'email' => 'required_without:phone|nullable|email|confirmed'
        ]);

        $account = new Account;
        $account->username = $request->get('username');
        $account->email = $request->get('email');
        $account->activated = false;
        $account->domain = config('app.sip_domain');
        $account->ip_address = $request->ip();
        $account->creation_time = Carbon::now();
        $account->user_agent = config('app.name');
        $account->save();

        if ($request->filled('phone')) {
            $alias = new Alias;
            $alias->alias = $request->get('phone');
            $alias->domain = config('app.sip_domain');
            $alias->account_id = $account->id;
            $alias->save();

            $account->confirmation_key = Utils::generatePin();
            $account->save();

            $ovhSMS = new OvhSMS;
            $ovhSMS->send($request->get('phone'), 'Your '.config('app.name').' validation code is '.$account->confirmation_key);

            return view('account.authenticate_phone', [
                'account' => $account
            ]);
        }

        $account->confirmation_key = Str::random($this->emailCodeSize);
        $account->save();

        Mail::to($account)->send(new RegisterConfirmation($account));

        return view('account.authenticate_email', [
            'account' => $account
        ]);
    }

    public function delete(Request $request)
    {
        return view('account.delete', [
            'account' => $request->user()
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['identifier' => 'required|same:identifier_confirm']);

        Auth::logout();
        $request->user()->delete();

        return redirect()->route('account.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $account = Account::where('username', $request->get('username'))
                          ->firstOrFail();

        // Try out the passwords
        foreach ($account->passwords as $password) {
            if (hash_equals(
                $password->password,
                Utils::bchash($request->get('username'), config('app.sip_domain'), $request->get('password'), $password->algorithm)
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

        Mail::to($account)->send(new PasswordAuthentication($account));

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

        // Ask the user to set a password
        if (!$account->activated) {
            return redirect()->route('account.password');
        }

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

        // Try alias
        if (!$account) {
            $alias = Alias::where('alias', $request->get('phone'))->first();

            if ($alias) {
                $account = $alias->account;
            }
        }

        if (!$account) {
            return view('account.login_phone')->withErrors([
                'phone' => 'Phone number not found'
            ]);
        }

        $account->confirmation_key = Utils::generatePin();
        $account->save();

        $ovhSMS = new OvhSMS;
        $ovhSMS->send($request->get('phone'), 'Your '.config('app.name').' validation code is '.$account->confirmation_key);

        // Ask the user to set a password
        if (!$account->activated) {
            return redirect()->route('account.password');
        }

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
