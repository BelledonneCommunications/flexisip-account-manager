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

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\SipDomain;
use Illuminate\Validation\Rule;

class SipDomainController extends Controller
{
    public function index()
    {
        return view('admin.sip_domain.index', ['sip_domains' => SipDomain::withCount('accounts')->get()]);
    }

    public function create()
    {
        return view('admin.sip_domain.create_edit', [
            'sip_domain' => new SipDomain
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required|unique:sip_domains',
        ]);

        $sipDomain = new SipDomain;
        $sipDomain->domain = $request->get('domain');
        $sipDomain = $this->setConfig($request, $sipDomain);
        $sipDomain->save();

        return redirect()->route('admin.sip_domains.index');
    }

    public function edit(int $id)
    {
        return view('admin.sip_domain.create_edit', [
            'sip_domain' => SipDomain::findOrFail($id)
        ]);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'max_account' => 'required|integer',
        ]);

        $sipDomain = SipDomain::findOrFail($id);
        $sipDomain = $this->setConfig($request, $sipDomain);
        $sipDomain->save();

        return redirect()->back();
    }

    private function setConfig(Request $request, SipDomain $sipDomain)
    {
        $request->validate([
            'max_account' => 'required|integer',
        ]);

        $sipDomain->super = getRequestBoolean($request, 'super');
        $sipDomain->disable_chat_feature = getRequestBoolean($request, 'disable_chat_feature');
        $sipDomain->disable_meetings_feature = getRequestBoolean($request, 'disable_meetings_feature');
        $sipDomain->disable_broadcast_feature = getRequestBoolean($request, 'disable_broadcast_feature');
        $sipDomain->hide_settings = getRequestBoolean($request, 'hide_settings');
        $sipDomain->max_account = $request->get('max_account', 0);
        $sipDomain->hide_account_settings = getRequestBoolean($request, 'hide_account_settings');
        $sipDomain->disable_call_recordings_feature = getRequestBoolean($request, 'disable_call_recordings_feature');
        $sipDomain->only_display_sip_uri_username = getRequestBoolean($request, 'only_display_sip_uri_username');
        $sipDomain->assistant_hide_create_account = getRequestBoolean($request, 'assistant_hide_create_account');
        $sipDomain->assistant_disable_qr_code = getRequestBoolean($request, 'assistant_disable_qr_code');
        $sipDomain->assistant_hide_third_party_account = getRequestBoolean($request, 'assistant_hide_third_party_account');

        return $sipDomain;
    }

    public function delete(int $id)
    {
        return view('admin.sip_domain.delete', [
            'sip_domain' => SipDomain::findOrFail($id)
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $sipDomain = SipDomain::findOrFail($id);

        $request->validate([
            'domain' => [
                'required',
                Rule::in(['first-zone', $sipDomain->domain]),
            ]
        ]);

        $sipDomain->delete();

        return redirect()->route('admin.sip_domains.index');
    }
}
