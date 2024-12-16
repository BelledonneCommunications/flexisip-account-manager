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
            'domain' => 'required|unique:spaces|regex:/'. Space::DOMAIN_REGEX . '/',
            'host' => 'nullable|regex:/'. Space::HOST_REGEX . '/',
            'full_host' => 'required|unique:spaces,host',
        ]);

        $space = new Space();
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

        $space = $this->setConfig($request, $space);
        $space->save();

        return redirect()->back();
    }

    public function parameters(Space $space)
    {
        return view('admin.space.parameters', [
            'space' => $space
        ]);
    }

    public function parametersUpdate(Request $request, Space $space)
    {
        $request->validate([
            'max_accounts' => 'required|integer|min:0',
            'expire_at' => 'nullable|date|after_or_equal:today'
        ]);

        if ($request->get('max_accounts') > 0) {
            $request->validate([
                'max_accounts' => 'integer|min:' . $space->accounts()->count()
            ]);
        }

        $space->super = getRequestBoolean($request, 'super');
        $space->max_accounts = $request->get('max_accounts');
        $space->expire_at = $request->get('expire_at');
        $space->save();

        return redirect()->route('admin.spaces.show', $space);
    }

    private function setConfig(Request $request, Space $space)
    {
        $request->validate([
            'max_account' => 'required|integer',
        ]);

        $space->disable_chat_feature = getRequestBoolean($request, 'disable_chat_feature');
        $space->disable_meetings_feature = getRequestBoolean($request, 'disable_meetings_feature');
        $space->disable_broadcast_feature = getRequestBoolean($request, 'disable_broadcast_feature');
        $space->hide_settings = getRequestBoolean($request, 'hide_settings');
        $space->max_account = $request->get('max_account', 0);
        $space->hide_account_settings = getRequestBoolean($request, 'hide_account_settings');
        $space->disable_call_recordings_feature = getRequestBoolean($request, 'disable_call_recordings_feature');
        $space->only_display_sip_uri_username = getRequestBoolean($request, 'only_display_sip_uri_username');
        $space->assistant_hide_create_account = getRequestBoolean($request, 'assistant_hide_create_account');
        $space->assistant_disable_qr_code = getRequestBoolean($request, 'assistant_disable_qr_code');
        $space->assistant_hide_third_party_account = getRequestBoolean($request, 'assistant_hide_third_party_account');

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
