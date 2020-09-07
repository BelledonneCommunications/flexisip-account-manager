<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Account;
use App\Password;
use App\Helpers\Utils;

class AccountController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:external.accounts,username|filled',
            'algorithm' => 'required|in:SHA-256,MD5',
            'password' => 'required|filled',
            'domain' => 'min:3',
        ]);

        $algorithm = $request->has('password_sha256') ? 'SHA-256' : 'MD5';

        $account = new Account;
        $account->username = $request->get('username');
        $account->email = $request->get('email');
        $account->activated = true;
        $account->domain = $request->has('domain')
            ? $request->get('domain')
            : config('app.sip_domain');
        $account->ip_address = $request->ip();
        $account->creation_time = Carbon::now();
        $account->user_agent = config('app.name');
        $account->save();

        $password = new Password;
        $password->account_id = $account->id;
        $password->password = Utils::bchash($account->username, $account->domain, $request->get('password'), $request->get('algorithm'));
        $password->algorithm = $request->get('algorithm');
        $password->save();

        return response()->json($account);
    }
}
