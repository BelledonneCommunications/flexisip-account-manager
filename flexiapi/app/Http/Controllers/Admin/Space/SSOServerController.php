<?php

namespace App\Http\Controllers\Admin\Space;

use App\Space;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
        $space = Space::findOrFail($spaceId);

        if (!$space->refreshSSOCertificate()) {
            return redirect()->back()->withErrors([
                'sso_public_key' => __('The public key cannot be refreshed')
            ]);
        }

        $space->save();

        return redirect()->back();
    }

    public function store(Request $request, int $spaceId)
    {
        $request->validate([
            'sso_server_url' => 'required|url|ends_with:/',
            'sso_realm' => 'required',
            'sso_sip_identifier' => 'required'
        ]);

        $space = Space::findOrFail($spaceId);
        $space->sso_server_url = $request->get('sso_server_url');
        $space->sso_realm = $request->get('sso_realm');
        $space->sso_sip_identifier = $request->get('sso_sip_identifier');

        if ($space->refreshSSOCertificate()) {
            $space->save();
        } else {
            return redirect()->back()->withErrors([
                'sso_public_key' => __('The public key cannot be refreshed')
            ]);
        }

        return redirect()->route('admin.spaces.integration', $spaceId);
    }
}
