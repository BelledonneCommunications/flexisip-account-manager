<?php

namespace App\Http\Controllers\Account;

use App\Account;
use App\Alias;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AccountService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class RecoveryController extends Controller
{
    public function showEmail(Request $request)
    {
        return view('account.recovery.show', [
            'method' => 'email',
            'domain' => resolveDomain($request)
        ]);
    }

    public function showPhone(Request $request)
    {
        return view('account.recovery.show', [
            'method' => 'phone',
            'domain' => resolveDomain($request)
        ]);
    }

    public function send(Request $request)
    {
        $rules = [
            'email' => 'required_without:phone|email|exists:accounts,email',
            'phone' => 'required_without:email|starts_with:+',
            //'g-recaptcha-response'  => 'required|captcha',
        ];

        if ($request->get('email')) {
            if (config('app.account_email_unique') == false) {
                $rules['username'] = 'required';
            }

            $request->validate($rules);

            $account = Account::where('email', $request->get('email'));

            /**
             * Because several accounts can have the same email
             */
            if (config('app.account_email_unique') == false) {
                $account = $account->where('username', $request->get('username'));
            }

            $account = $account->first();

            // Try alias
            if (!$account) {
                $alias = Alias::where('alias', $request->get('username'))->first();

                if ($alias && $alias->account->email == $request->get('email')) {
                    $account = $alias->account;
                }
            }
        } elseif ($request->get('phone')) {
            $account = Account::where('username', $request->get('phone'))->first();

            // Try alias
            if (!$account) {
                $alias = Alias::where('alias', $request->get('phone'))->first();

                if ($alias) {
                    $account = $alias->account;
                }
            }
        }

        if (!$account) {
            return redirect()->back()->withErrors(['identifier' => 'The account doesn\'t exists']);
        }

        if ($request->get('email')) {
            $account = (new AccountService)->recoverByEmail($account);
        } elseif ($request->get('phone')) {
            $account = (new AccountService)->recoverByPhone($account);
        }

        return view('account.recovery.confirm', [
            'account_id' => Crypt::encryptString($account->id)
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'account_id' => 'required',
            'code' => 'required|digits:4'
        ]);

        $account = Account::where('id', Crypt::decryptString($request->get('account_id')))->firstOrFail();

        if ($account->recovery_code != $request->get('code')) {
            return redirect()->back()->withErrors([
                'code' => 'Wrong code'
            ]);
        }

        $account->recovery_code = null;
        $account->save();

        Auth::login($account);
        return redirect()->route('account.dashboard');
    }
}
