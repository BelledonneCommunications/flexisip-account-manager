<?php

namespace App\Http\Controllers\Admin\Space;

use App\Space;
use App\Account;
use App\SpaceOIDCAuthenticationConfiguration;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OIDCServerController extends Controller
{
    public function show(Space $space)
    {
        return view('admin.space.oidc_server.show', [
            'space' => $space,
            'accountWithoutEmail' => Account::withoutGlobalScopes()
                ->whereNull('email')
                ->where('domain', $space->domain)
                ->count(),
        ]);
    }

    public function store(Request $request, Space $space)
    {
        $request->validate([
            'audience' => 'required',
            'authorization_server' => 'required|url|ends_with:/',
            'sip_id_claim' => 'required',
            'client_id' => 'required'
        ]);

        if ($space->unique_email) {
            $oidcAuthenticationConfiguration = $space->oidcAuthenticationConfiguration ?: new SpaceOIDCAuthenticationConfiguration;

            $oidcAuthenticationConfiguration->authorization_server = $request->input('authorization_server');
            $oidcAuthenticationConfiguration->sip_id_claim = $request->input('sip_id_claim');
            $oidcAuthenticationConfiguration->audience = $request->input('audience');
            $oidcAuthenticationConfiguration->client_id = $request->input('client_id');
            $oidcAuthenticationConfiguration->client_secret = $request->input('client_secret');
            $oidcAuthenticationConfiguration->space_id = $space->id;

            if ($oidcAuthenticationConfiguration->refreshOIDCCertificate() > 0) {
                $oidcAuthenticationConfiguration->save();
            } else {
                $error = __('Unable to connect to the OIDC server or find valid certificates. Please verify the provided settings.');

                if ($oidcAuthenticationConfiguration->refreshKeysErrors !== null) {
                    $error .= " (" . $oidcAuthenticationConfiguration->refreshKeysErrors . ')';
                }

                return redirect()->back()->withErrors([
                    'refresh_keys_errors' => $error
                ])->withInput();
            }
        }

        return redirect()->route('admin.spaces.integration', $space->domain);
    }

    public function delete(Space $space)
    {
        return view('admin.space.oidc_server.delete', ['space' => $space->domain]);
    }

    public function destroy(Space $space)
    {
        DB::table('space_sso_servers')
            ->where('space_id', $space->id)
            ->delete();

        return redirect()->route('admin.spaces.integration', $space->domain);
    }
}
