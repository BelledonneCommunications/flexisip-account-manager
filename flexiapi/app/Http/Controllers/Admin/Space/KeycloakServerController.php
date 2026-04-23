<?php

namespace App\Http\Controllers\Admin\Space;

use App\Space;
use App\SpaceEmailServer;

use App\Http\Requests\EmailServer\CreateUpdate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KeycloakServerController extends Controller
{
    public function show(int $spaceId)
    {
        return view('admin.space.keycloak_server.show', [
            'space' => Space::findOrFail($spaceId)
        ]);
    }

    public function refreshPublicKey(int $spaceId)
    {
        $space = Space::findOrFail($spaceId);

        if (!$space->refreshKeycloakCertificate()) {
            return redirect()->back()->withErrors([
                'keycloak_public_key' => __('The public key cannot be refreshed')
            ]);
        }

        $space->save();

        return redirect()->back();
    }

    public function store(Request $request, int $spaceId)
    {
        $request->validate([
            'keycloak_server_url' => 'required|url|ends_with:/',
            'keycloak_realm' => 'required',
            'keycloak_sip_identifier' => 'required'
        ]);

        $space = Space::findOrFail($spaceId);
        $space->keycloak_server_url = $request->get('keycloak_server_url');
        $space->keycloak_realm = $request->get('keycloak_realm');
        $space->keycloak_sip_identifier = $request->get('keycloak_sip_identifier');

        if ($space->refreshKeycloakCertificate()) {
            $space->save();
        } else {
            return redirect()->back()->withErrors([
                'keycloak_public_key' => __('The public key cannot be refreshed')
            ]);
        }

        return redirect()->route('admin.spaces.integration', $spaceId);
    }
}
