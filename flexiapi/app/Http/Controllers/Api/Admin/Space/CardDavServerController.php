<?php

namespace App\Http\Controllers\Api\Admin\Space;

use App\Http\Controllers\Controller;
use App\Http\Requests\Space\CardDavServer;
use Illuminate\Http\Request;

use App\Space;
use App\SpaceCardDavServer;

class CardDavServerController extends Controller
{
    public function index(string $domain)
    {
        return Space::where('domain', $domain)->firstOrFail()->carddavServers;
    }

    public function show(string $domain, int $carddavServerId)
    {
        return Space::where('domain', $domain)->firstOrFail()->carddavServers()->findOrFail($carddavServerId);
    }

    public function store(CardDavServer $request, string $domain)
    {
        $space = Space::where('domain', $domain)->firstOrFail();

        $carddavServer = new SpaceCardDavServer;
        $carddavServer->space_id = $space->id;
        $carddavServer->fill($request->validated());
        $carddavServer->enabled = $request->has('enabled') && (bool)$request->get('enabled');
        $carddavServer->use_exact_match_policy = $request->has('use_exact_match_policy') && (bool)$request->get('use_exact_match_policy');

        return $carddavServer->save();
    }

    public function update(CardDavServer $request, string $domain, int $carddavServerId)
    {
        $space = Space::where('domain', $domain)->firstOrFail();

        $carddavServer = $space->carddavServers()->findOrFail($carddavServerId);
        $carddavServer->fill($request->validated());
        $carddavServer->enabled = $request->has('enabled') && (bool)$request->get('enabled');
        $carddavServer->use_exact_match_policy = $request->has('use_exact_match_policy') && (bool)$request->get('use_exact_match_policy');

        return $carddavServer->save();
    }

    public function destroy(string $domain, int $carddavServerId)
    {
        $space = Space::where('domain', $domain)->firstOrFail();

        $carddavServer = $space->carddavServers()->findOrFail($carddavServerId);
        return $carddavServer->delete();
    }
}
