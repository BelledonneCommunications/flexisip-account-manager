<?php

namespace App\Http\Controllers\Admin\Space;

use App\Space;
use App\SpaceEmailServer;
use App\Http\Requests\EmailServer\CreateUpdate;
use App\Http\Controllers\Controller;

class EmailServerController extends Controller
{
    public function show(Space $space)
    {
        return view('admin.space.email_server.show', [
            'space' => $space,
            'emailServer' => $space->emailServer ?? new SpaceEmailServer
        ]);
    }

    public function store(CreateUpdate $request, Space $space)
    {
        $emailServer = $space->emailServer ?? new SpaceEmailServer;

        $emailServer->space_id = $space->id;
        $emailServer->host = $request->input('host');
        $emailServer->port = $request->input('port');
        $emailServer->username = $request->input('username');
        $emailServer->password = $request->input('password');
        $emailServer->from_address = $request->input('from_address') ?? null;
        $emailServer->from_name = $request->input('from_name') ?? null;
        $emailServer->signature = $request->input('signature') ?? null;

        $emailServer->save();

        return redirect()->route('admin.spaces.integration', $space->domain);
    }

    public function delete(Space $space)
    {
        return view('admin.space.email_server.delete', [
            'space' => $space
        ]);
    }

    public function destroy(Space $space)
    {
        $space->emailServer->delete();

        return redirect()->route('admin.spaces.integration', $space->domain);
    }
}
