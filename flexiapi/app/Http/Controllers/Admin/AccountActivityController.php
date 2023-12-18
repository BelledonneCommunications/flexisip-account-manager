<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Account;

class AccountActivityController extends Controller
{
    public function index(Request $request, Account $account)
    {
        return view(
            'admin.account.activity.index',
            [
                'account' => $account
            ]
        );
    }
}
