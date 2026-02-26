<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Http\Controllers\Admin\Account\CallForwardingController as AdminCallForwardingController;

class CallForwardingController extends Controller
{
    public function update(Request $request)
    {
        return (new AdminCallForwardingController)->update($request, $request->user()->id);
    }
}
