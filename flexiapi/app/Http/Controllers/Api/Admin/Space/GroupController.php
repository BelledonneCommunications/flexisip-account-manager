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

namespace App\Http\Controllers\Api\Admin\Space;

use App\Group;
use App\Space;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Rules\BlacklistedUsername;
use App\Rules\IsNotPhoneNumber;
use App\Rules\NoUppercase;
use App\Rules\SIPUsername;

class GroupController extends Controller
{
    public function index(Request $request, string $domain)
    {
        $space = Space::where('domain', $domain)->firstOrFail();
        return $space->groups;
    }

    public function show(Request $request, string $domain, int $groupId)
    {
        $space = Space::where('domain', $domain)->firstOrFail();
        return $space->groups()->findOrFail($groupId);
    }

    public function store(Request $request, string $domain)
    {
        $request->validate([
            'name' => ['required', 'unique:groups'],
            'username' => [
                'required',
                'unique:groups',
                'unique:accounts',
                new NoUppercase,
                new IsNotPhoneNumber,
                new BlacklistedUsername,
                new SIPUsername
            ],
            'strategy' => ['string', Rule::in(array_keys(Group::STRATEGIES))],
        ]);

        $space = Space::where('domain', $domain)->firstOrFail();

        $group = new Group;
        $group->name = $request->name;
        $group->username = $request->username;
        $group->strategy = $request->strategy;
        $group->space_id = $space->id;
        $group->save();

        return $group;
    }

    public function update(Request $request, string $domain, int $groupId)
    {
        $request->validate([
            'name' => ['required', Rule::unique('groups')->ignore($groupId)],
            'strategy' => ['string', Rule::in(array_keys(Group::STRATEGIES))],
        ]);

        $group = Group::findOrFail($groupId);

        $group->name = $request->name;
        $group->strategy = $request->strategy;
        $group->save();

        return $group;
    }

    public function destroy(string $domain, int $groupId)
    {
        $space = Space::where('domain', $domain)->firstOrFail();
        $space->groups()->where('id', $groupId)->firstOrFail()->delete();

        return response()->noContent();
    }

    public function attach(string $domain, int $groupId, int $accountId)
    {
        $space = Space::where('domain', $domain)->firstOrFail();
        $group = $space->groups()->findOrFail($groupId);
        $group->accounts()->syncWithoutDetaching($accountId);

        return $group;
    }

    public function detach(string $domain, int $groupId, int $accountId)
    {
        $space = Space::where('domain', $domain)->firstOrFail();
        $group = $space->groups()->findOrFail($groupId);
        $group->accounts()->detach($accountId);

        return $group;
    }
}
