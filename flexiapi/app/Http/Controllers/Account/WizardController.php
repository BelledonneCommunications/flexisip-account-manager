<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Wizard;
use hisorange\BrowserDetect\Parser as Browser;
use Illuminate\Http\Request;
use Illuminate\Support\Uri;

class WizardController extends Controller
{
    public function documentation(Request $request)
    {
        return view('wizard.documentation', [
            'documentation' => markdownDocumentation(view('wizard.documentation_markdown', [
                'app_name' => space()->name
            ])->render())
        ]);
    }

    public function show(Request $request, ?string $token = null)
    {
        $sip = '';
        $queryParams = ['linphone-action' => 'show'];
        $wizard = null;

        if ($token) {
            $wizard = Wizard::where('token', $token)
                ->whereNull('used_at')
                ->first();

            if ($wizard) {
                if ($wizard->sip) {
                    $sip = $wizard->sip;
                }

                if ($wizard->provisioning_account_id) {
                    $queryParams['linphone-fetch-config'] = route('provisioning.provision', $wizard->provisionedAccount->provision());
                }

                if ($wizard->linphone_action) {
                    $queryParams['linphone-action'] = $wizard->linphone_action;
                }

                $wizard->update(['used_at' => now()]);
            }
        } else {
            if ($request->has('sip') && isSip($request->input('sip'))) {
                $sip = $request->input('sip');
            }

            if ($request->has('linphone-action')
                && \in_array($request->input('linphone-action'), Wizard::LINPHONE_ACTION)
            ) {
                $queryParams['linphone-action'] = $request->input('linphone-action');
            }
        }

        if ($wizard?->linphone_use_sips ?? $request->has('linphone-use-sips')) {
            $queryParams['linphone-use-sips'] = 'true';
        }

        return view('wizard.show', [
            'uri' => Uri::of('sip-linphone:' . stripSipProtocol($sip))->withQuery($queryParams),
            'platform' => Browser::platformFamily()
        ]);
    }
}
