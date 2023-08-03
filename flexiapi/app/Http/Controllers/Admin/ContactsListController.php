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

use App\ContactsList;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class ContactsListController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'order_by' => 'in:title,updated_at,contacts_count',
            'order_sort' => 'in:asc,desc',
        ]);

        $contactsLists = ContactsList::orderBy($request->get('order_by', 'updated_at'), $request->get('order_sort', 'desc'));

        return view('admin.contacts_list.index', [
            'contacts_lists' => $contactsLists
                ->paginate(20)
                ->appends($request->query()),
        ]);
    }

    public function show(int $id)
    {
    }

    public function create(Request $request)
    {
        return view('admin.contacts_list.create_edit', [
            'contacts_list' => new ContactsList,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:contacts_lists'
        ]);

        $contactsList = new ContactsList;
        $contactsList->title = $request->get('title');
        $contactsList->description = $request->get('description');
        $contactsList->save();

        return redirect()->route('admin.contacts_lists.edit', $contactsList->id);
    }

    public function edit(int $id)
    {
        return view('admin.contacts_list.create_edit', [
            'contacts_list' => ContactsList::findOrFail($id),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'title' => [
                'required',
                Rule::unique('contacts_lists')->ignore($id),
            ],
        ]);

        $contactsList = ContactsList::findOrFail($id);
        $contactsList->title = $request->get('title');
        $contactsList->description = $request->get('description');
        $contactsList->save();

        return redirect()->route('admin.contacts_lists.index');
    }


    public function delete(int $id)
    {
        return view('admin.contacts_list.delete', [
            'contacts_list' => ContactsList::findOrFail($id),
        ]);
    }

    public function destroy(Request $request)
    {
        $contactsList = ContactsList::findOrFail($request->get('contacts_lists_id'));
        $contactsList->delete();

        return redirect()->route('admin.contacts_lists.index');
    }
}
