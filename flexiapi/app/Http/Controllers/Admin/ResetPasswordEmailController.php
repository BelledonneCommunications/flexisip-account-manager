<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\ResetPasswordEmailToken;
use App\Http\Controllers\Controller;
use App\Mail\ResetPassword;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ResetPasswordEmailController extends Controller
{
    public function create(int $accountId)
    {
        $account = Account::findOrFail($accountId);

        return view('admin.account.reset_password_email.create', [
            'account' => $account
        ]);
    }

    public function send(int $accountId)
    {
        $account = Account::findOrFail($accountId);

        $resetPasswordEmail = new ResetPasswordEmailToken;
        $resetPasswordEmail->account_id = $account->id;
        $resetPasswordEmail->token = Str::random(16);
        $resetPasswordEmail->email = $account->email;
        $resetPasswordEmail->save();

        Mail::to($account)->send(new ResetPassword($resetPasswordEmail));

        return redirect()->route('admin.account.activity.index', $account);
    }
}
