<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Admin\Account\VoicemailController as AdminVoicemailController;
use Illuminate\Http\Request;

class VoicemailController extends Controller
{
    public function index(Request $request)
    {
        return (new AdminVoicemailController)->index($request, $request->user()->id);
    }

    public function store(Request $request)
    {
        return (new AdminVoicemailController)->store($request, $request->user()->id);
    }

    public function show(Request $request, string $uuid)
    {
        return (new AdminVoicemailController)->show($request, $request->user()->id, $uuid);
    }

    public function destroy(Request $request, string $uuid)
    {
        return (new AdminVoicemailController)->destroy($request, $request->user()->id, $uuid);
    }
}
