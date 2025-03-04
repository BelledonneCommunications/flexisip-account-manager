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
use App\Space;
use App\Rules\Ini;
use Illuminate\Validation\Rule;

class SpaceController extends Controller
{
    public function index()
    {
        return view('admin.space.index', ['spaces' => Space::withCount('accounts')->orderBy('host')->get()]);
    }

    public function me(Request $request)
    {
        return view('admin.space.show', [
            'space' => $request->user()->space
        ]);
    }

    public function show(Space $space)
    {
        return view('admin.space.show', [
            'space' => $space
        ]);
    }

    public function create()
    {
        return view('admin.space.create', [
            'space' => new Space()
        ]);
    }

    public function store(Request $request)
    {
        $fullHost = empty($request->get('host'))
            ? config('app.root_host')
            : $request->get('host') . '.' . config('app.root_host');

        $request->merge(['full_host' => $fullHost]);
        $request->validate([
            'name' => 'required|unique:spaces',
            'domain' => 'required|unique:spaces|regex:/'. Space::DOMAIN_REGEX . '/',
            'host' => 'nullable|regex:/'. Space::HOST_REGEX . '/',
            'full_host' => 'required|unique:spaces,host',
        ]);

        $space = new Space();
        $space->name = $request->get('name');
        $space->domain = $request->get('domain');
        $space->host = $request->get('full_host');
        $space->save();

        return redirect()->route('admin.spaces.index');
    }

    public function edit(Space $space)
    {
        return view('admin.space.edit', [
            'space' => $space
        ]);
    }

    public function update(Request $request, Space $space)
    {
        $request->validate([
            'max_account' => 'required|integer',
        ]);

        $space = $this->setAppConfiguration($request, $space);
        $space->save();

        return redirect()->back();
    }

    public function configuration(Space $space)
    {
        return view('admin.space.configuration', [
            'space' => $space
        ]);
    }

    public function configurationUpdate(Request $request, Space $space)
    {
        $space = $this->setConfiguration($request, $space);
        $space->save();

        return redirect()->route('admin.spaces.configuration', $space);
    }

    public function administration(Space $space)
    {
        return view('admin.space.administration', [
            'space' => $space
        ]);
    }

    public function administrationUpdate(Request $request, Space $space)
    {
        $request->validate([
            'name' => ['required', Rule::unique('spaces')->ignore($space->id)],
            'max_accounts' => 'required|integer|min:0',
            'expire_at' => 'nullable|date|after_or_equal:today'
        ]);

        if ($request->get('max_accounts') > 0) {
            $request->validate([
                'max_accounts' => 'integer|min:' . $space->accounts()->count()
            ]);
        }

        $space->name = $request->get('name');
        $space->super = getRequestBoolean($request, 'super');
        $space->max_accounts = $request->get('max_accounts');
        $space->expire_at = $request->get('expire_at');
        $space->custom_theme = getRequestBoolean($request, 'custom_theme');
        $space->web_panel = getRequestBoolean($request, 'web_panel');
        $space->save();

        return redirect()->route('admin.spaces.show', $space);
    }

    private function setConfiguration(Request $request, Space $space)
    {
        $request->validate([
            'newsletter_registration_address' => 'nullable|email',
            'custom_provisioning_entries' => ['nullable', new Ini]
        ]);

        $space->copyright_text = $request->get('copyright_text');
        $space->intro_registration_text = $request->get('intro_registration_text');
        $space->confirmed_registration_text = $request->get('confirmed_registration_text');
        $space->newsletter_registration_address = $request->get('newsletter_registration_address');
        $space->account_proxy_registrar_address = $request->get('account_proxy_registrar_address');
        $space->account_realm = $request->get('account_realm');
        $space->custom_provisioning_entries = $request->get('custom_provisioning_entries');
        $space->custom_provisioning_overwrite_all = getRequestBoolean($request, 'custom_provisioning_overwrite_all');
        $space->provisioning_use_linphone_provisioning_header = getRequestBoolean($request, 'provisioning_use_linphone_provisioning_header');

        $space->public_registration = getRequestBoolean($request, 'public_registration');
        $space->phone_registration = getRequestBoolean($request, 'phone_registration');
        $space->intercom_features = getRequestBoolean($request, 'intercom_features');

        return $space;
    }

    private function setAppConfiguration(Request $request, Space $space)
    {
        $request->validate([
            'max_account' => 'required|integer',
        ]);

        $space->disable_chat_feature = getRequestBoolean($request, 'disable_chat_feature', reversed: true);
        $space->disable_meetings_feature = getRequestBoolean($request, 'disable_meetings_feature', reversed: true);
        $space->disable_broadcast_feature = getRequestBoolean($request, 'disable_broadcast_feature', reversed: true);
        $space->hide_settings = getRequestBoolean($request, 'hide_settings', reversed: true);
        $space->max_account = $request->get('max_account', 0);
        $space->hide_account_settings = getRequestBoolean($request, 'hide_account_settings', reversed: true);
        $space->disable_call_recordings_feature = getRequestBoolean($request, 'disable_call_recordings_feature', reversed: true);
        $space->only_display_sip_uri_username = getRequestBoolean($request, 'only_display_sip_uri_username');
        $space->assistant_hide_create_account = getRequestBoolean($request, 'assistant_hide_create_account', reversed: true);
        $space->assistant_disable_qr_code = getRequestBoolean($request, 'assistant_disable_qr_code', reversed: true);
        $space->assistant_hide_third_party_account = getRequestBoolean($request, 'assistant_hide_third_party_account', reversed: true);

        return $space;
    }

    public function delete(Request $request, int $id)
    {
        $space = Space::findOrFail($id);
        return view('admin.space.delete', [
            'space' => $space
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $space = Space::findOrFail($id);

        $request->validate([
            'domain' => [
                'required',
                Rule::in(['first-zone', $space->domain]),
            ]
        ]);

        $space->delete();

        return redirect()->route('admin.spaces.index');
    }
}
