<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Admin\Account\CallForwardingController as AdminCallForwardingController;
use Illuminate\Http\Request;

class CallForwardingController extends Controller
{
    public function __construct(private AdminCallForwardingController $adminCallForwardingController)
    {
    }

    public function index(Request $request)
    {
        return $this->adminCallForwardingController->index($request, $request->user()->id);
    }

    public function store(Request $request)
    {
        return $this->adminCallForwardingController->store($request, $request->user()->id);
    }

    public function update(Request $request, $id)
    {
        return $this->adminCallForwardingController->update($request, $request->user()->id, $id);
    }

    public function show(Request $request, string $id)
    {
        return $this->adminCallForwardingController->show($request, $request->user()->id, $id);
    }

    public function destroy(Request $request, string $id)
    {
        return $this->adminCallForwardingController->destroy($request, $request->user()->id, $id);
    }
}
