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
            'super' => 'required|boolean',
        ]);

        $sipDomain = new SipDomain;
        $sipDomain->domain = $request->get('domain');
        $sipDomain->super = $request->has('super') ? (bool)$request->get('super') : false;
        $sipDomain->save();

        return $sipDomain;
    }

    public function show(string $domain)
    {
        return SipDomain::where('domain', $domain)->firstOrFail();
    }

    public function update(Request $request, string $domain)
    {
        $request->validate([
            'super' => 'required|boolean',
        ]);

        $sipDomain = SipDomain::where('domain', $domain)->firstOrFail();
        $sipDomain->super = $request->has('super') ? (bool)$request->get('super') : false;
        $sipDomain->save();

        return $sipDomain;
    }

    public function destroy(string $domain)
    {
        return SipDomain::where('domain', $domain)->delete();
    }
}
