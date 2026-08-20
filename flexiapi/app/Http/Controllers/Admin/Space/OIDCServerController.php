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

    public function refreshPublicKey(Space $space)
    {
        $oidcAuthenticationConfiguration = SpaceOIDCAuthenticationConfiguration::where('space_id', $space->id)->firstOrFail();

        if (!$oidcAuthenticationConfiguration->refreshOIDCCertificate()) {
            return redirect()->back()->withErrors([
                'sso_public_key' => __('The public key cannot be refreshed')
            ]);
        }

        $oidcAuthenticationConfiguration->save();

        return redirect()->back();
    }

    public function store(Request $request, Space $space)
    {
        $request->validate([
            'server_url' => 'required|url|ends_with:/',
            'sip_identifier' => 'required',
            'role_provisioning' => 'required_if:sso_auto_provisioning,on|nullable|string'
        ]);

        if ($space->unique_email) {
            $oidcAuthenticationConfiguration = $space->oidcAuthenticationConfiguration ?: new SpaceOIDCAuthenticationConfiguration;

            $oidcAuthenticationConfiguration->server_url = $request->input('server_url');
            $oidcAuthenticationConfiguration->sip_identifier = $request->input('sip_identifier');
            $oidcAuthenticationConfiguration->client_id = $request->input('client_id');
            $oidcAuthenticationConfiguration->client_secret = $request->input('client_secret');
            $oidcAuthenticationConfiguration->auto_provisioning = false;
            $oidcAuthenticationConfiguration->space_id = $space->id;

            if ($request->boolean('auto_provisioning')) {
                $oidcAuthenticationConfiguration->auto_provisioning = true;
                $oidcAuthenticationConfiguration->role_provisioning = $request->input('role_provisioning');
            }

            if ($oidcAuthenticationConfiguration->refreshOIDCCertificate()) {
                $oidcAuthenticationConfiguration->save();
            } else {
                $error = __('Unable to connect to the OIDC server. Please verify the provided settings.');

                if ($oidcAuthenticationConfiguration->refreshSsoError !== null) {
                    $error .= " (" . $oidcAuthenticationConfiguration->refreshSsoError . ')';
                }

                return redirect()->back()->withErrors([
                    'public_key' => $error
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
