<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Http\Controllers\Controller;
use App\Mail\Provisioning;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ProvisioningEmailController extends Controller
{
    public function create(int $accountId)
    {
        $account = Account::findOrFail($accountId);

        return view('admin.account.provisioning_email.create', [
            'account' => $account
        ]);
    }

    public function send(int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $account->provision();

        Mail::to($account)->send(new Provisioning($account));

        Log::info('Web Admin: Sending provisioning email', ['id' => $account->identifier]);

        return redirect()->route('admin.account.show', $account);
    }
}
