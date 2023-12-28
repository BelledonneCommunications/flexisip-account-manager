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

use App\Account;
use App\ContactsList;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactsListController extends Controller
{
    public function index(Request $request)
    {
        return ContactsList::all();
    }

    public function get(int $contactsListId)
    {
        return ContactsList::findOrFail($contactsListId);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required'],
            'description' => ['required']
        ]);

        $contactsList = new ContactsList;
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
        $contactsList->title = $request->get('title');
        $contactsList->description = $request->get('description');
        $contactsList->save();

        return $contactsList;
    }

    public function destroy(int $contactsListId)
    {
        return ContactsList::where('id', $contactsListId)
            ->delete();
    }

    public function contactAdd(int $id, int $contactId)
    {
        if (ContactsList::findOrFail($id)->contacts()->pluck('id')->contains($contactId)) {
            abort(403);
        }

        if (Account::findOrFail($contactId)) {
            return ContactsList::findOrFail($id)->contacts()->attach($contactId);
        }
    }

    public function contactRemove(int $id, int $contactId)
    {
        if (!ContactsList::findOrFail($id)->contacts()->pluck('id')->contains($contactId)) {
            abort(403);
        }

        return ContactsList::findOrFail($id)->contacts()->detach($contactId);
    }
}
