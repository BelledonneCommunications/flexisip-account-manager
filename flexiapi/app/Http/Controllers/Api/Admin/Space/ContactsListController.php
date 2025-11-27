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

use App\ContactsList;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactsListController extends Controller
{
    public function index(Request $request)
    {
        return $request->space->contactsLists;
    }

    public function get(Request $request, int $contactsListId)
    {
        return $request->space->contactsLists()->findOrFail($contactsListId);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required'],
            'description' => ['required']
        ]);

        $contactsList = new ContactsList;
        $contactsList->space_id = $request->space->id;
        $contactsList->title = $request->get('title');
        $contactsList->description = $request->get('description');
        $contactsList->save();

        return $contactsList;
    }

    public function update(Request $request, int $contactsListId)
    {
        $request->validate([
            'title' => ['required'],
            'description' => ['required']
        ]);

        $contactsList = ContactsList::findOrFail($contactsListId);
        $contactsList->space_id = $request->space->id;
        $contactsList->title = $request->get('title');
        $contactsList->description = $request->get('description');
        $contactsList->save();

        return $contactsList;
    }

    public function destroy(Request $request, int $contactsListId)
    {
        return $request->space->contactsLists()->where('id', $contactsListId)
            ->delete();
    }

    public function contactAdd(Request $request, int $id, int $contactId)
    {
        $contactsList = $request->space->contactsLists()->findOrFail($id);
        $contactsList->contacts()->detach($contactId);

        if ($request->space->accounts()->findOrFail($contactId)) {
            return $contactsList->contacts()->attach($contactId);
        }
    }

    public function contactRemove(Request $request, int $id, int $contactId)
    {
        $contactsList = $request->space->contactsLists()->findOrFail($id);

        if (!$contactsList->contacts()->pluck('id')->contains($contactId)) {
            abort(404);
        }

        return $contactsList->contacts()->detach($contactId);
    }
}
