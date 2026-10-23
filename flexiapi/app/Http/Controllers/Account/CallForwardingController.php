<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Account\CallForwardingController as AdminCallForwardingController;

class CallForwardingController extends Controller
{
    public function __construct(private AdminCallForwardingController $adminCallForwardingController)
    {
    }

    public function update(Request $request)
    {
        return $this->adminCallForwardingController->update($request, $request->user()->id);
    }
}
