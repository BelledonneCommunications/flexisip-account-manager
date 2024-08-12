<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2020 Belledonne Communications SARL, All rights reserved.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace App\Http\Controllers\Account;

use App\Account;
use App\AuthToken;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Writer\PngWriter;

class ProvisioningController extends Controller
{
    public function documentation(Request $request)
    {
        return view('provisioning.documentation', [
            'documentation' => markdownDocumentationView('provisioning.documentation_markdown')
        ]);
    }

    public function qrcode(Request $request, string $provisioningToken)
    {
        $account = Account::withoutGlobalScopes()
            ->where('id', function ($query) use ($provisioningToken) {
                $query->select('account_id')
                      ->from('provisioning_tokens')
                      ->where('used', false)
                      ->where('token', $provisioningToken);
            })
            ->firstOrFail();

        if ($account->activationExpired()) abort(404);

        $params = ['provisioning_token' => $provisioningToken];

        if ($request->has('reset_password')) {
            $params['reset_password'] = true;
        }

        $url = route('provisioning.provision', $params);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(300)
            ->margin(10)
            ->build();

        return response($result->getString())
            ->header('Content-Type', $result->getMimeType())
            ->header('X-Qrcode-URL', $url);
    }

    /**
     * auth_token based provisioning
     */
    public function authToken(Request $request, string $token)
    {
        $authToken = AuthToken::where('token', $token)->valid()->firstOrFail();

        if ($authToken->account) {
            $account = $authToken->account;
            $authToken->delete();

            return $this->generateProvisioning($request, $account);
        }

        abort(404);
    }

    /**
     * Authenticated provisioning
     */
    public function me(Request $request)
    {
        $this->checkProvisioningHeader($request);

        return $this->generateProvisioning($request, $request->user());
    }

    /**
     * Get the base provisioning, with authentication
     */
    public function show(Request $request)
    {
        $this->checkProvisioningHeader($request);

        return $this->generateProvisioning($request);
    }

    /**
     * Provisioning Token based provisioning
     */
    public function provision(Request $request, string $provisioningToken)
    {
        $this->checkProvisioningHeader($request);

        $account = Account::withoutGlobalScopes()
            ->where('id', function ($query) use ($provisioningToken) {
                $query->select('account_id')
                    ->from('provisioning_tokens')
                    ->where('used', false)
                    ->where('token', $provisioningToken);
            })
            ->firstOrFail();

        if ($account->activationExpired() || ($provisioningToken != $account->provisioning_token)) {
            return abort(404);
        }

        if ($account->currentProvisioningToken->expired()) {
            return abort(410, 'Expired');
        }

        $account->activated = true;
        $account->currentProvisioningToken->consume();
        $account->save();

        return $this->generateProvisioning($request, $account);
    }

    private function checkProvisioningHeader(Request $request)
    {
        if (!$request->hasHeader('x-linphone-provisioning')
          && config('app.provisioning_use_x_linphone_provisioning_header')) {
            abort(400, 'x-linphone-provisioning header is missing');
        }
    }

    private function generateProvisioning(Request $request, Account $account = null)
    {
        // Load the hooks if they exists
        $provisioningHooks = config_path('provisioning_hooks.php');

        if (file_exists($provisioningHooks)) {
            require_once($provisioningHooks);
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $xpath = new \DOMXpath($dom);
        $config = $dom->createElement('config');
        $config->setAttribute('xmlns', 'http://www.linphone.org/xsds/lpconfig.xsd');

        $dom->appendChild($config);

        // Default RC file handling
        $rcFile = config('app.provisioning_rc_file');

        if (file_exists($rcFile)) {
            $rc = parse_ini_file($rcFile, true);

            foreach ($rc as $sectionName => $values) {
                $section = $dom->createElement('section');
                $section->setAttribute('name', $sectionName);

                foreach ($values as $key => $value) {
                    $entry = $dom->createElement('entry', $value);
                    $entry->setAttribute('name', $key);
                    $section->appendChild($entry);
                }

                $config->appendChild($section);
            }
        }

        // Password reset
        if ($account && $request->has('reset_password')) {
            $account->updatePassword(Str::random(10));
        }

        $section = $dom->createElement('section');
        $section->setAttribute('name', 'misc');

        $entry = $dom->createElement('entry', route('account.contacts.vcard.index'));
        $entry->setAttribute('name', 'contacts-vcard-list');
        $section->appendChild($entry);

        $config->appendChild($section);

        if ($account) {
            $ui = $xpath->query("//section[@name='ui']")->item(0);

            if ($ui == null && $account->sipDomain) {
                $section = $dom->createElement('section');
                $section->setAttribute('name', 'ui');

                foreach ([
                    'super',
                    'disable_chat_feature',
                    'disable_meetings_feature',
                    'disable_broadcast_feature',
                    'hide_settings',
                    'hide_account_settings',
                    'disable_call_recordings_feature',
                    'only_display_sip_uri_username',
                    'assistant_hide_create_account',
                    'assistant_disable_qr_code',
                    'assistant_hide_third_party_account',
                    'max_account',
                ] as $key) {
                    // Cast the booleans into integers
                    $entry = $dom->createElement('entry', (int)$account->sipDomain->$key);
                    $entry->setAttribute('name', $key);
                    $section->appendChild($entry);
                }

                $config->appendChild($section);
            }

            $section = $xpath->query("//section[@name='proxy_0']")->item(0);

            if ($section == null) {
                $section = $dom->createElement('section');
                $section->setAttribute('name', 'proxy_0');
            }

            $entry = $dom->createElement('entry', $account->fullIdentifier);
            $entry->setAttribute('name', 'reg_identity');
            $section->appendChild($entry);

            // Complete the section with the Proxy hook
            if (function_exists('provisioningProxyHook')) {
                provisioningProxyHook($section, $request, $account);
            }

            $config->appendChild($section);

            $passwords = $account->passwords()->get();
            $authInfoIndex = 0;

            foreach ($passwords as $password) {
                $section = $xpath->query("//section[@name='auth_info_" . $authInfoIndex . "']")->item(0);

                if ($section == null) {
                    $section = $dom->createElement('section');
                    $section->setAttribute('name', 'auth_info_' . $authInfoIndex);
                }

                $entry = $dom->createElement('entry', $account->username);
                $entry->setAttribute('name', 'username');
                $section->appendChild($entry);

                $entry = $dom->createElement('entry', $account->domain);
                $entry->setAttribute('name', 'domain');
                $section->appendChild($entry);

                $entry = $dom->createElement('entry', $password->password);
                $entry->setAttribute('name', 'ha1');
                $section->appendChild($entry);

                $entry = $dom->createElement('entry', $account->resolvedRealm);
                $entry->setAttribute('name', 'realm');
                $section->appendChild($entry);

                $entry = $dom->createElement('entry', $password->algorithm);
                $entry->setAttribute('name', 'algorithm');
                $section->appendChild($entry);

                // Complete the section with the Auth hook
                if (function_exists('provisioningAuthHook')) {
                    provisioningAuthHook($section, $request, $password);
                }

                $config->appendChild($section);

                $authInfoIndex++;
            }
        }

        // Complete the section with the Auth hook
        if (function_exists('provisioningAdditionalSectionHook')) {
            provisioningAdditionalSectionHook($config, $request, $account);
        }

        // Overwrite all the entries
        if (config('app.provisioning_overwrite_all')) {
            $xpath = new \DOMXpath($dom);
            $entries = $xpath->query("//section/entry");
            if (!is_null($entries)) {
                foreach ($entries as $entry) {
                    $entry->setAttribute('overwrite', 'true');
                }
            }
        }

        return response($dom->saveXML($dom->documentElement))->header('Content-Type', 'application/xml');
    }
}
