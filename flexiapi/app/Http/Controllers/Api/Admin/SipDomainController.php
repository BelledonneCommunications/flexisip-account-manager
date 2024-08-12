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

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\SipDomain;

class SipDomainController extends Controller
{
    public function index()
    {
        return SipDomain::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required|unique:sip_domains',
        ]);

        $sipDomain = new SipDomain;
        $sipDomain->domain = $request->get('domain');
        $this->setRequestBoolean($request, $sipDomain, 'super');
        $this->setRequestBoolean($request, $sipDomain, 'disable_chat_feature');
        $this->setRequestBoolean($request, $sipDomain, 'disable_meetings_feature');
        $this->setRequestBoolean($request, $sipDomain, 'disable_broadcast_feature');
        $this->setRequestBoolean($request, $sipDomain, 'hide_settings');
        $this->setRequestBoolean($request, $sipDomain, 'hide_account_settings');
        $this->setRequestBoolean($request, $sipDomain, 'disable_call_recordings_feature');
        $this->setRequestBoolean($request, $sipDomain, 'only_display_sip_uri_username');
        $this->setRequestBoolean($request, $sipDomain, 'assistant_hide_create_account');
        $this->setRequestBoolean($request, $sipDomain, 'assistant_disable_qr_code');
        $this->setRequestBoolean($request, $sipDomain, 'assistant_hide_third_party_account');
        $sipDomain->max_account = $request->get('max_account', 0);

        $sipDomain->save();

        return $sipDomain->refresh();
    }

    public function show(string $domain)
    {
        return SipDomain::where('domain', $domain)->firstOrFail();
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
        ]);

        $sipDomain = SipDomain::where('domain', $domain)->firstOrFail();
        $sipDomain->super = $request->get('super');
        $sipDomain->disable_chat_feature = $request->get('disable_chat_feature');
        $sipDomain->disable_meetings_feature = $request->get('disable_meetings_feature');
        $sipDomain->disable_broadcast_feature = $request->get('disable_broadcast_feature');
        $sipDomain->hide_settings = $request->get('hide_settings');
        $sipDomain->hide_account_settings = $request->get('hide_account_settings');
        $sipDomain->disable_call_recordings_feature = $request->get('disable_call_recordings_feature');
        $sipDomain->only_display_sip_uri_username = $request->get('only_display_sip_uri_username');
        $sipDomain->assistant_hide_create_account = $request->get('assistant_hide_create_account');
        $sipDomain->assistant_disable_qr_code = $request->get('assistant_disable_qr_code');
        $sipDomain->assistant_hide_third_party_account = $request->get('assistant_hide_third_party_account');
        $sipDomain->max_account = $request->get('max_account', 0);
        $sipDomain->save();

        return $sipDomain;
    }

    private function setRequestBoolean(Request $request, SipDomain $sipDomain, string $key)
    {
        if ($request->has($key)) {
            $sipDomain->$key = (bool)$request->get($key);
        }
    }

    public function destroy(string $domain)
    {
        return SipDomain::where('domain', $domain)->delete();
    }
}
