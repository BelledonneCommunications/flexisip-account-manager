<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use \App\Http\Controllers\Api\Admin\Account\CallForwardingController as AdminCallForwardingController;
use Illuminate\Http\Request;

class CallForwardingController extends Controller
{
    public function index(Request $request)
    {
        return (new AdminCallForwardingController)->index($request, $request->user()->id);
    }

    public function store(Request $request)
    {
        return (new AdminCallForwardingController)->store($request, $request->user()->id);
    }

    public function update(Request $request, $id)
    {
        return (new AdminCallForwardingController)->update($request, $request->user()->id, $id);
    }

    public function show(Request $request, string $id)
    {
        return (new AdminCallForwardingController)->show($request, $request->user()->id, $id);
    }

    public function destroy(Request $request, string $id)
    {
        return (new AdminCallForwardingController)->destroy($request, $request->user()->id, $id);
    }
}
