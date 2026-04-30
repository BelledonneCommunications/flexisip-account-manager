<?php

namespace App\Http\Controllers\Account;

use Illuminate\Support\Uri;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Wizard;

use hisorange\BrowserDetect\Parser as Browser;

class WizardController extends Controller
{
    public function show(Request $request, string $token = null)
    {
        $uriString = 'sip-linphone:';
        $queryParams = [];
        $action = null;
        $useSips = false;

        if ($token) {
            $wizard = Wizard::where('token', $token)
                ->whereNull('used_at')
                ->first();

            if ($wizard) {
                if ($wizard->sip) {
                    $uriString .= $wizard->sip;
                }

                if ($wizard->provisioning_account_id) {
                    $provToken = $wizard->provisionedAccount->provision();
                    $queryParams['linphone-fetch-config'] = route('provisioning.provision', $provToken);
                }

                if ($wizard->linphone_action) {
                    $queryParams['linphone-action'] = $wizard->linphone_action;
                    $action = $wizard->linphone_action;
                }

                if ($wizard->linphone_use_sips) {
                    $useSips = true;
                }

                $this->consume($token);
            } else {
                $queryParams['linphone-action'] = 'show';
                $action = 'show';
            }
        } else {
            if ($request->input('sip')) {
                $uriString .= $request->input('sip');
            }

            if ($request->input('linphone-action')) {
                if (in_array($request->input('linphone-action'), Wizard::LINPHONE_ACTION)) {
                    $queryParams['linphone-action'] = $request->input('linphone-action');
                    $action = $request->input('linphone-action');
                }
            } else {
                $queryParams['linphone-action'] = 'show';
                $action = $request->input('show');
            }

            if ($request->has('linphone-use-sips')) {
                $useSips = true;
            }
        }

        $uri = Uri::of($uriString)->withQuery($queryParams);

        if ($useSips) {
            $uri .= '&linphone-use-sips';
        }

        return view('wizard.show', [
            'uri' => $uri,
            'action' => $action,
            'platform' => Browser::platformFamily()
        ]);
    }

    public function consume(string $token)
    {
        $wizard = Wizard::where('token', $token)
            ->whereNull('used_at')
            ->firstOrFail();

        $wizard->update(['used_at' => now()]);
    }
}
