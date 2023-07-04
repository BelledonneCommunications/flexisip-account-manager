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

use App\Account;
use App\ContactsList;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactsListContactController extends Controller
{
    public function add(Request $request, int $contactsListId)
    {
        $accounts = Account::orderBy('updated_at', $request->get('updated_at_order', 'desc'))
            ->with('externalAccount');

        if ($request->has('search')) {
            $accounts = $accounts->where('username', 'like', '%' . $request->get('search') . '%');
        }

        return view('admin.contacts_list.contacts.add', [
            'contacts_list' => ContactsList::firstOrFail($contactsListId),
            'params' => [
                'search' => $request->get('search'),
                'contacts_list_id' => $contactsListId,
                'updated_at_order' => $request->get('updated_at_order') == 'desc' ? 'asc' : 'desc'
            ],
            'accounts' => $accounts->whereNotIn('id', function ($query) use ($contactsListId) {
                $query->select('contact_id')
                    ->from('contacts_list_contact')
                    ->where('contacts_list_id', $contactsListId);
            })->paginate(20)->appends($request->query()),
        ]);
    }

    public function search(Request $request, int $contactsListId)
    {
        return redirect()->route('admin.contacts_lists.contacts.add', ['contacts_list_id' => $contactsListId] + $request->except('_token'));
    }

    public function store(Request $request, int $contactsListId)
    {
        $request->validate([
            'contacts_ids' => 'required|exists:accounts,id'
        ]);

        $contactsList = ContactsList::firstOrFail($contactsListId);
        $contactsList->contacts()->detach($request->get('contacts_ids')); // Just in case
        $contactsList->contacts()->attach($request->get('contacts_ids'));

        return redirect()->route('admin.contacts_lists.edit', $contactsList->id);
    }

    public function destroy(Request $request, int $contactsListId)
    {
        $request->validate([
            'contacts_ids' => 'required|exists:accounts,id'
        ]);

        $contactsList = ContactsList::findOrFail($contactsListId);
        $contactsList->contacts()->detach($request->get('contacts_ids'));

        return redirect()->route('admin.contacts_lists.edit', $contactsList->id);
    }
}
