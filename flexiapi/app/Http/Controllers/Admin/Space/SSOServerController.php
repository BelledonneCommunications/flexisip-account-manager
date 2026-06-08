<?php

namespace App\Http\Controllers\Admin\Space;

use App\Space;
use App\SpaceSsoServer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SSOServerController extends Controller
{
    public function show(int $spaceId)
    {
        return view('admin.space.sso_server.show', [
            'space' => Space::findOrFail($spaceId)
        ]);
    }

    public function refreshPublicKey(int $spaceId)
    {
        $ssoServer = SpaceSsoServer::where('space_id', $spaceId)->firstOrFail();

        if (!$ssoServer->refreshSSOCertificate()) {
            return redirect()->back()->withErrors([
                'sso_public_key' => __('The public key cannot be refreshed')
            ]);
        }

        $ssoServer->save();

        return redirect()->back();
    }

    public function store(Request $request, Space $space)
    {
        $request->validate([
            'server_url' => 'required|url|ends_with:/',
            'realm' => 'required',
            'sip_identifier' => 'required',
            'role_provisioning' => 'required_if:sso_auto_provisioning,on|nullable|string'
        ]);

        if ($space->unique_email) {
            $ssoServer = $space->ssoServer ?: new SpaceSsoServer;

            $ssoServer->server_url = $request->get('server_url');
            $ssoServer->realm = $request->get('realm');
            $ssoServer->sip_identifier = $request->get('sip_identifier');
            $ssoServer->client_id = $request->get('client_id');
            $ssoServer->client_secret = $request->get('client_secret');
            $ssoServer->auto_provisioning = false;
            $ssoServer->space_id = $space->id;

            if ($request->boolean('auto_provisioning')) {
                $ssoServer->auto_provisioning = true;
                $ssoServer->role_provisioning = $request->get('role_provisioning');
            }

            if ($ssoServer->refreshSSOCertificate()) {
                $ssoServer->save();
            } else {
                return redirect()->back()->withErrors([
                    'public_key' => __('The public key cannot be refreshed')
                ]);
            }
        }

        return redirect()->route('admin.spaces.integration', $space->id);
    }

    public function delete(Space $space)
    {
        return view('admin.space.sso_server.delete', ['space' => $space]);
    }

    public function destroy(Space $space)
    {
        DB::table('space_sso_servers')
            ->where('space_id', $space->id)
            ->delete();

        return redirect()->route('admin.spaces.integration', $space);
    }
}
