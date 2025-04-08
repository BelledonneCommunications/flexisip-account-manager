<?php

namespace App\Http\Controllers\Admin\Space;

use App\Space;
use App\SpaceEmailServer;

use App\Http\Requests\EmailServer\CreateUpdate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailServerController extends Controller
{
    public function show(int $spaceId)
    {
        $space = Space::findOrFail($spaceId);

        return view('admin.space.email_server.show', [
            'space' => $space,
            'emailServer' => $space->emailServer ?? new SpaceEmailServer
        ]);
    }

    public function store(CreateUpdate $request, int $spaceId)
    {
        $space = Space::findOrFail($spaceId);
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

        return redirect()->route('admin.spaces.integration', $spaceId);
    }

    public function delete(int $spaceId)
    {
        $space = Space::findOrFail($spaceId);

        return view('admin.space.email_server.delete', [
            'space' => $space
        ]);
    }

    public function destroy(int $spaceId)
    {
        $space = Space::findOrFail($spaceId);
        $space->emailServer->delete();

        return redirect()->route('admin.spaces.integration', $spaceId);
    }
}
