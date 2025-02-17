<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2023 Belledonne Communications SARL, All rights reserved.

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

namespace App\Http\Controllers\Api\Admin;

use App\Space;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SpaceController extends Controller
{
    public function index()
    {
        return Space::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required|unique:spaces',
            'host' => 'required|unique:spaces',
            'max_accounts' => 'nullable|integer',
            'expire_at' => 'nullable|date|after_or_equal:today'
        ]);

        $space = new Space;
        $space->domain = $request->get('domain');
        $space->host = $request->get('host');
        $this->setRequestBoolean($request, $space, 'super');
        $this->setRequestBoolean($request, $space, 'disable_chat_feature');
        $this->setRequestBoolean($request, $space, 'disable_meetings_feature');
        $this->setRequestBoolean($request, $space, 'disable_broadcast_feature');
        $this->setRequestBoolean($request, $space, 'hide_settings');
        $this->setRequestBoolean($request, $space, 'hide_account_settings');
        $this->setRequestBoolean($request, $space, 'disable_call_recordings_feature');
        $this->setRequestBoolean($request, $space, 'only_display_sip_uri_username');
        $this->setRequestBoolean($request, $space, 'assistant_hide_create_account');
        $this->setRequestBoolean($request, $space, 'assistant_disable_qr_code');
        $this->setRequestBoolean($request, $space, 'assistant_hide_third_party_account');
        $space->max_account = $request->get('max_account', 0);
        $space->max_accounts = $request->get('max_accounts', 0);
        $space->expire_at = $request->get('expire_at');

        $space->copyright_text = $request->get('copyright_text');
        $space->intro_registration_text = $request->get('intro_registration_text');
        $space->confirmed_registration_text = $request->get('confirmed_registration_text');
        $space->newsletter_registration_address = $request->get('newsletter_registration_address');
        $space->account_proxy_registrar_address = $request->get('account_proxy_registrar_address');
        $space->account_realm = $request->get('account_realm');
        $space->custom_provisioning_entries = $request->get('custom_provisioning_entries');
        $this->setRequestBoolean($request, $space, 'custom_provisioning_overwrite_all');
        $this->setRequestBoolean($request, $space, 'provisioning_use_linphone_provisioning_header');
        $this->setRequestBoolean($request, $space, 'custom_theme');
        $this->setRequestBoolean($request, $space, 'web_panel');
        $this->setRequestBoolean($request, $space, 'public_registration');
        $this->setRequestBoolean($request, $space, 'phone_registration');
        $this->setRequestBoolean($request, $space, 'intercom_features');

        $space->save();

        return $space->refresh();
    }

    public function show(string $domain)
    {
        return Space::where('domain', $domain)->firstOrFail();
    }

    public function update(Request $request, string $domain)
    {
        $request->validate([
            'super' => 'required|boolean',
            'disable_chat_feature' => 'required|boolean',
            'disable_meetings_feature' => 'required|boolean',
            'disable_broadcast_feature' => 'required|boolean',
            'hide_settings' => 'required|boolean',
            'hide_account_settings' => 'required|boolean',
            'disable_call_recordings_feature' => 'required|boolean',
            'only_display_sip_uri_username' => 'required|boolean',
            'assistant_hide_create_account' => 'required|boolean',
            'assistant_disable_qr_code' => 'required|boolean',
            'assistant_hide_third_party_account' => 'required|boolean',
            'max_account' => 'required|integer',
            'max_accounts' => 'required|integer',
            'expire_at' => 'nullable|date|after_or_equal:today',

            'custom_provisioning_overwrite_all' => 'required|boolean',
            'provisioning_use_linphone_provisioning_header' => 'required|boolean',
            'custom_theme' => 'required|boolean',
            'web_panel' => 'required|boolean',
            'public_registration' => 'required|boolean',
            'phone_registration' => 'required|boolean',
            'intercom_features' => 'required|boolean',
        ]);

        $space = Space::where('domain', $domain)->firstOrFail();

        if ($request->get('max_accounts') > 0) {
            $request->validate([
                'max_accounts' => 'integer|min:' . $space->accounts()->count()
            ]);
        }

        $request->validate([
            'host' => ['required', Rule::unique('spaces')->ignore($space->id)]
        ]);

        $space->host = $request->get('host');
        $space->super = $request->get('super');
        $space->disable_chat_feature = $request->get('disable_chat_feature');
        $space->disable_meetings_feature = $request->get('disable_meetings_feature');
        $space->disable_broadcast_feature = $request->get('disable_broadcast_feature');
        $space->hide_settings = $request->get('hide_settings');
        $space->hide_account_settings = $request->get('hide_account_settings');
        $space->disable_call_recordings_feature = $request->get('disable_call_recordings_feature');
        $space->only_display_sip_uri_username = $request->get('only_display_sip_uri_username');
        $space->assistant_hide_create_account = $request->get('assistant_hide_create_account');
        $space->assistant_disable_qr_code = $request->get('assistant_disable_qr_code');
        $space->assistant_hide_third_party_account = $request->get('assistant_hide_third_party_account');
        $space->max_account = $request->get('max_account', 0);
        $space->max_accounts = $request->get('max_accounts', 0);
        $space->expire_at = $request->get('expire_at');

        $space->copyright_text = $request->get('copyright_text');
        $space->intro_registration_text = $request->get('intro_registration_text');
        $space->confirmed_registration_text = $request->get('confirmed_registration_text');
        $space->newsletter_registration_address = $request->get('newsletter_registration_address');
        $space->account_proxy_registrar_address = $request->get('account_proxy_registrar_address');
        $space->account_realm = $request->get('account_realm');
        $space->custom_provisioning_entries = $request->get('custom_provisioning_entries');
        $space->custom_provisioning_overwrite_all = $request->get('custom_provisioning_overwrite_all');
        $space->provisioning_use_linphone_provisioning_header = $request->get('provisioning_use_linphone_provisioning_header');
        $space->custom_theme = $request->get('custom_theme');
        $space->web_panel = $request->get('web_panel');
        $space->public_registration = $request->get('public_registration');
        $space->phone_registration = $request->get('phone_registration');
        $space->intercom_features = $request->get('intercom_features');

        $space->save();

        return $space;
    }

    private function setRequestBoolean(Request $request, Space $space, string $key)
    {
        if ($request->has($key)) {
            $space->$key = (bool)$request->get($key);
        }
    }

    public function destroy(string $domain)
    {
        return Space::where('domain', $domain)->delete();
    }
}
