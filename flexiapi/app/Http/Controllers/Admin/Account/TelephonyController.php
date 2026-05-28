<?php

namespace App\Http\Controllers\Admin\Account;

use App\Account;
use App\Http\Controllers\Controller;

class TelephonyController extends Controller
{
    public function show(int $accountId)
    {
        return view('admin.account.telephony.show', [
            'account' => Account::findOrFail($accountId)
        ]);
    }
}
