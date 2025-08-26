<?php

namespace App\Http\Controllers\Api\Admin\Space;

use App\Http\Controllers\Controller;
use App\Http\Requests\Space\CardDavServer;
use Illuminate\Http\Request;

use App\Space;
use App\SpaceCardDavServer;

class CardDavServerController extends Controller
{
    public function index(string $host)
    {
        return Space::where('host', $host)->firstOrFail()->carddavServers;
    }

    public function show(string $host, int $carddavServerId)
    {
        return Space::where('host', $host)->firstOrFail()->carddavServers()->findOrFail($carddavServerId);
    }

    public function store(CardDavServer $request, string $host)
    {
        $space = Space::where('host', $host)->firstOrFail();

        $carddavServer = new SpaceCardDavServer;
        $carddavServer->space_id = $space->id;
        $carddavServer->fill($request->validated());
        $carddavServer->enabled = $request->has('enabled') && (bool)$request->get('enabled');
        $carddavServer->use_exact_match_policy = $request->has('use_exact_match_policy') && (bool)$request->get('use_exact_match_policy');

        return $carddavServer->save();
    }

    public function update(CardDavServer $request, string $host, int $carddavServerId)
    {
        $space = Space::where('host', $host)->firstOrFail();

        $carddavServer = $space->carddavServers()->findOrFail($carddavServerId);
        $carddavServer->fill($request->validated());
        $carddavServer->enabled = $request->has('enabled') && (bool)$request->get('enabled');
        $carddavServer->use_exact_match_policy = $request->has('use_exact_match_policy') && (bool)$request->get('use_exact_match_policy');

        return $carddavServer->save();
    }

    public function destroy(string $host, int $carddavServerId)
    {
        $space = Space::where('host', $host)->firstOrFail();

        $carddavServer = $space->carddavServers()->findOrFail($carddavServerId);
        return $carddavServer->delete();
    }
}
