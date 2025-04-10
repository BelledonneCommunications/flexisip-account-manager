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

namespace App\Http\Controllers\Admin;

use App\ApiKey;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApiKeyController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.api_key.index', [
            'api_keys' => $this->getApiKeysQuery($request)->with('account')->get()
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.api_key.create', [
            'account' => $request->user()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3',
            'expires_after_last_used_minutes' => 'integer|min:0'
        ]);

        $apiKey = new ApiKey;
        $apiKey->account_id = $request->user()->id;
        $apiKey->name = $request->get('name');
        $apiKey->expires_after_last_used_minutes = $request->get('expires_after_last_used_minutes');
        $apiKey->last_used_at = Carbon::now();
        $apiKey->key = Str::random(40);
        $apiKey->save();

        return redirect()->route('admin.api_keys.index');
    }

    public function delete(Request $request, string $key)
    {
        return view('admin.api_key.delete', [
            'api_key' => $this->getApiKeysQuery($request)->where('key', $key)->first()
        ]);
    }

    public function destroy(Request $request)
    {
        $this->getApiKeysQuery($request)->where('key', $request->get('key'))->delete();

        return redirect()->route('admin.api_keys.index');
    }

    private function getApiKeysQuery(Request $request)
    {
        $apiKeys = ApiKey::whereIn('account_id', function ($query) {
            $query->select('id')
                  ->from('accounts')
                  ->where('admin', true);
        })->whereNotNull('expires_after_last_used_minutes');

        if (!$request->user()->superAdmin) {
            $apiKeys->whereIn('account_id', function ($query) use ($request) {
                $query->select('id')
                      ->from('accounts')
                      ->where('domain', $request->user()->domain);
            });
        }

        return $apiKeys;
    }
}
