<?php

namespace App\Http\Controllers\Admin\Space;

use App\Http\Controllers\Controller;
use App\Http\Requests\Space\CardDavServer;
use Illuminate\Http\Request;

use App\Space;
use App\SpaceCardDavServer;

class CardDavServerController extends Controller
{
    public function create(Space $space)
    {
        return view('admin.space.carddav_server.create_edit', [
            'space' => $space,
            'carddavServer' => new SpaceCardDavServer
        ]);
    }

    public function store(CardDavServer $request, Space $space)
    {
        $carddavServer = new SpaceCardDavServer;
        $carddavServer->space_id = $space->id;
        $carddavServer->fill($request->validated());
        $carddavServer->enabled = getRequestBoolean($request, 'enabled');
        $carddavServer->use_exact_match_policy = getRequestBoolean($request, 'use_exact_match_policy');
        $carddavServer->save();

        return redirect()->route('admin.spaces.integration', $space);
    }

    public function edit(Space $space, int $carddavServerId)
    {
        return view('admin.space.carddav_server.create_edit', [
            'space' => $space,
            'carddavServer' => $space->carddavServers()->findOrFail($carddavServerId)
        ]);
    }

    public function update(CardDavServer $request, Space $space, int $carddavServerId)
    {
        $carddavServer = $space->carddavServers()->findOrFail($carddavServerId);
        $carddavServer->fill($request->validated());
        $carddavServer->enabled = getRequestBoolean($request, 'enabled');
        $carddavServer->use_exact_match_policy = getRequestBoolean($request, 'use_exact_match_policy');
        $carddavServer->save();

        return redirect()->route('admin.spaces.integration', $space);
    }

    public function delete(Space $space, int $carddavServerId)
    {
        return view('admin.space.carddav_server.delete', [
            'space' => $space,
            'carddavServer' => $space->carddavServers()->findOrFail($carddavServerId)
        ]);
    }

    public function destroy(Space $space, int $carddavServerId)
    {
        $carddavServer = $space->carddavServers()->findOrFail($carddavServerId);
        $carddavServer->delete();

        return redirect()->route('admin.spaces.integration', $space->id);
    }
}
