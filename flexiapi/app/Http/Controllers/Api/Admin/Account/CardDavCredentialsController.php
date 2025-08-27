<?php

namespace App\Http\Controllers\Api\Admin\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Account;
use App\Space;
use App\AccountCardDavCredentials;
use App\SpaceCardDavServer;
use App\Http\Requests\Account\CardDavCredentials;

class CardDavCredentialsController extends Controller
{
    public function index(Request $request, int $accountId)
    {
        $account = $request->space->accounts()->findOrFail($accountId);
        $cardDavServers = $account->carddavServers;

        if ($cardDavServers->isEmpty()) return new \stdClass;

        return $cardDavServers->map(function ($cardDavServer) {
            return $this->extractCardDavServer($cardDavServer);
        })->keyBy('carddav_id');
    }

    public function show(Request $request, int $accountId, int $cardDavServerId)
    {
        $account = $request->space->accounts()->findOrFail($accountId);
        $cardDavServer = $account->cardDavServers()->findOrFail($cardDavServerId);

        return $this->extractCardDavServer($cardDavServer);
    }

    public function update(CardDavCredentials $request, int $accountId, int $cardDavServerId)
    {
        $account = $request->space->accounts()->findOrFail($accountId);
        $cardDavServer = $request->space->cardDavServers()->findOrFail($cardDavServerId);

        $accountCarddavCredentials = AccountCardDavCredentials::where('account_id', $account->id)
            ->where('space_carddav_server_id', $cardDavServer->id)
            ->delete();

        $accountCarddavCredentials = new AccountCardDavCredentials;
        $accountCarddavCredentials->space_carddav_server_id = $cardDavServer->id;
        $accountCarddavCredentials->account_id = $account->id;
        $accountCarddavCredentials->username = $request->get('username');
        $accountCarddavCredentials->realm = $request->get('realm');
        $accountCarddavCredentials->password = bchash(
            $request->get('username'),
            $request->get('realm'),
            $request->get('password'),
            $request->get('algorithm')
        );
        $accountCarddavCredentials->algorithm = $request->get('algorithm');
        return $accountCarddavCredentials->save();
    }

    public function destroy(Request $request, int $accountId, int $cardDavServerId)
    {
        $account = $request->space->accounts()->findOrFail($accountId);
        $cardDavServer = $account->cardDavServers()->findOrFail($cardDavServerId);

        return $cardDavServer->delete();
    }

    private function extractCardDavServer(SpaceCardDavServer $cardDavServer)
    {
        return [
            'carddav_id' => $cardDavServer->id,
            'username' => $cardDavServer->pivot->username,
            'realm' => $cardDavServer->pivot->realm,
            'algorithm' => $cardDavServer->pivot->algorithm,
            'password' => $cardDavServer->pivot->password,
        ];
    }
}
