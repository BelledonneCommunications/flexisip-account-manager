<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailServer\CreateUpdate;
use App\Space;
use App\SpaceEmailServer;

use Illuminate\Http\Request;

class EmailServerController extends Controller
{
    public function show(string $host)
    {
        return Space::where('host', $host)->firstOrFail()->emailServer()->firstOrFail();
    }

    public function store(CreateUpdate $request, string $host)
    {
        $space = Space::where('host', $host)->firstOrFail();
        $emailServer = $space->emailServer ?? new SpaceEmailServer;

        $emailServer->space_id = $space->id;
        $emailServer->host = $request->get('host');
        $emailServer->port = $request->get('port');
        $emailServer->username = $request->get('username');
        $emailServer->password = $request->get('password');
        $emailServer->from_address = $request->get('from_address') ?? null;
        $emailServer->from_name = $request->get('from_name') ?? null;
        $emailServer->signature = $request->get('signature') ?? null;

        $emailServer->save();

        return $emailServer;
    }

    public function destroy(string $host)
    {
        $space = Space::where('host', $host)->firstOrFail();
        return $space->emailServer->delete();
    }
}
