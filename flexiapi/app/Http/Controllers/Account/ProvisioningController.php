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
    public function qrcode(Request $request, $provisioningToken)
    {
        $account = Account::withoutGlobalScopes()
            ->where('provisioning_token', $provisioningToken)
            ->firstOrFail();

        if ($account->activationExpired()) abort(404);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data(route('provisioning.show', ['provisioning_token' => $provisioningToken]))
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(300)
            ->margin(10)
            ->build();

        return response($result->getString())->header('Content-Type', $result->getMimeType());
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

            return $this->show($request, null, $account);
        }

        abort(404);
    }

    /**
     * Authenticated provisioning
     */
    public function me(Request $request)
    {
        return $this->show($request, null, $request->user());
    }

    public function show(Request $request, $provisioningToken = null, Account $requestAccount = null)
    {
        // Load the hooks if they exists
        $provisioningHooks = config_path('provisioning_hooks.php');

        if (file_exists($provisioningHooks)) {
            require_once($provisioningHooks);
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $config = $dom->createElement('config');
        $config->setAttribute('xmlns', 'http://www.linphone.org/xsds/lpconfig.xsd');
        //$config->setAttribute('xsi:schemaLocation', 'http://www.linphone.org/xsds/lpconfig.xsd lpconfig.xsd');
        //$config->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        $dom->appendChild($config);

        // Default RC file handling
        $rcFile = config('app.provisioning_rc_file');
        $proxyConfigIndex = 0;
        $authInfoIndex = 0;

        if (file_exists($rcFile)) {
            $rc = parse_ini_file($rcFile, true);

            foreach ($rc as $sectionName => $values) {
                $section = $dom->createElement('section');
                $section->setAttribute('name', $sectionName);

                if (Str::startsWith($sectionName, "proxy_config_")) {
                    $proxyConfigIndex++;
                } elseif (Str::startsWith($sectionName, "auth_info_")) {
                    $authInfoIndex++;
                }

                foreach ($values as $key => $value) {
                    $entry = $dom->createElement('entry', $value);
                    $entry->setAttribute('name', $key);
                    $section->appendChild($entry);
                }

                $config->appendChild($section);
            }
        }

        $account = null;

        // Account handling
        if ($requestAccount) {
            $account = $requestAccount;
        } elseif ($provisioningToken) {
            $account = Account::withoutGlobalScopes()
                ->where('provisioning_token', $provisioningToken)
                ->first();
        }

        $section = $dom->createElement('section');
        $section->setAttribute('name', 'misc');

        $entry = $dom->createElement('entry', route('account.contacts.vcard.index'));
        $entry->setAttribute('name', 'contacts-vcard-list');
        $section->appendChild($entry);

        $config->appendChild($section);

        if ($account && !$account->activationExpired()) {
            $externalAccount = $account->externalAccount;

            $section = $dom->createElement('section');
            $section->setAttribute('name', 'proxy_' . $proxyConfigIndex);

            $entry = $dom->createElement('entry', $account->fullIdentifier);
            $entry->setAttribute('name', 'reg_identity');
            $section->appendChild($entry);

            $entry = $dom->createElement('entry', 1);
            $entry->setAttribute('name', 'reg_sendregister');
            $section->appendChild($entry);

            $entry = $dom->createElement('entry', 'push_notification');
            $entry->setAttribute('name', 'refkey');
            $section->appendChild($entry);

            // Complete the section with the Proxy hook
            if (function_exists('provisioningProxyHook')) {
                provisioningProxyHook($section, $request, $account);
            }

            if ($externalAccount) {
                $entry = $dom->createElement('entry', 'external_account');
                $entry->setAttribute('name', 'depends_on');
                $section->appendChild($entry);
            }

            $config->appendChild($section);

            $passwords = $account->passwords()->get();

            foreach ($passwords as $password) {
                $section = $dom->createElement('section');
                $section->setAttribute('name', 'auth_info_' . $authInfoIndex);

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

            if ($provisioningToken) {
                // Activate the account
                if ($account->activated == false
                    && $provisioningToken == $account->provisioning_token
                ) {
                    $account->activated = true;
                }

                $account->provisioning_token = null;
                $account->save();
            }

            $proxyConfigIndex++;

            // External Account handling
            if ($externalAccount) {
                $section = $dom->createElement('section');
                $section->setAttribute('name', 'proxy_' . $proxyConfigIndex);

                $entry = $dom->createElement('entry', '<sip:' . $externalAccount->identifier . '>');
                $entry->setAttribute('name', 'reg_identity');
                $section->appendChild($entry);

                $entry = $dom->createElement('entry', 1);
                $entry->setAttribute('name', 'reg_sendregister');
                $section->appendChild($entry);

                $entry = $dom->createElement('entry', 'push_notification');
                $entry->setAttribute('name', 'refkey');
                $section->appendChild($entry);

                $entry = $dom->createElement('entry', 'external_account');
                $entry->setAttribute('name', 'idkey');
                $section->appendChild($entry);

                $config->appendChild($section);

                $section = $dom->createElement('section');
                $section->setAttribute('name', 'auth_info_' . $authInfoIndex);

                $entry = $dom->createElement('entry', $externalAccount->username);
                $entry->setAttribute('name', 'username');
                $section->appendChild($entry);

                $entry = $dom->createElement('entry', $externalAccount->domain);
                $entry->setAttribute('name', 'domain');
                $section->appendChild($entry);

                $entry = $dom->createElement('entry', $externalAccount->password);
                $entry->setAttribute('name', 'ha1');
                $section->appendChild($entry);

                $entry = $dom->createElement('entry', $externalAccount->resolvedRealm);
                $entry->setAttribute('name', 'realm');
                $section->appendChild($entry);

                $entry = $dom->createElement('entry', $externalAccount->algorithm);
                $entry->setAttribute('name', 'algorithm');
                $section->appendChild($entry);

                $config->appendChild($section);
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
