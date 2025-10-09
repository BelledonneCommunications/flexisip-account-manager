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

namespace App\Http\Controllers\Admin\Space;

use App\Account;
use App\ContactsList;
use App\Space;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class ContactsListController extends Controller
{
    public function index(Request $request, Space $space)
    {
        $request->validate([
            'order_by' => 'in:title,updated_at,contacts_count',
            'order_sort' => 'in:asc,desc',
        ]);

        $contactsLists = $space->contactsLists()->orderBy($request->get('order_by', 'updated_at'), $request->get('order_sort', 'desc'));

        return view('admin.space.contacts_list.index', [
            'space' => $space,
            'contacts_lists' => $contactsLists
                ->paginate(20)
                ->appends($request->query()),
        ]);
    }

    public function create(Request $request, Space $space)
    {
        return view('admin.space.contacts_list.create_edit', [
            'space' => $space,
            'contacts_list' => new ContactsList,
        ]);
    }

    public function store(Request $request, Space $space)
    {
        $request->validate([
            'title' => 'required|unique:contacts_lists'
        ]);

        $contactsList = new ContactsList;
        $contactsList->space_id = $space->id;
        $contactsList->title = $request->get('title');
        $contactsList->description = $request->get('description');
        $contactsList->save();

        return redirect()->route('admin.spaces.contacts_lists.edit', [$space, $contactsList->id]);
    }

    public function search(Request $request, Space $space, int $contactsListId)
    {
        return redirect()->route('admin.spaces.contacts_lists.edit', [
            'space' => $space,
            'contacts_list_id' => $contactsListId] + $request->except('_token'));
    }

    public function edit(Request $request, Space $space, int $id)
    {
        $contacts = $space->contactsLists()->findOrFail($id)->contacts();

        if ($request->has('search')) {
            $contacts = $contacts->where('username', 'like', '%' . $request->get('search') . '%');
        }

        if ($request->has('domain')) {
            $contact = $contacts->where('domain', $request->get('domain'));
        }

        $contacts = $contacts->get();

        return view('admin.space.contacts_list.create_edit', [
            'space' => $space,
            'domains' => Account::groupBy('domain')->pluck('domain'),
            'contacts_list' => $space->contactsLists()->findOrFail($id),
            'contacts' => $contacts
        ]);
    }

    public function update(Request $request, Space $space, int $id)
    {
        $request->validate([
            'title' => [
                'required',
                Rule::unique('contacts_lists')->ignore($id),
            ],
        ]);

        $contactsList = $space->contactsLists()->findOrFail($id);
        $contactsList->title = $request->get('title');
        $contactsList->description = $request->get('description');
        $contactsList->save();

        return redirect()->route('admin.spaces.contacts_lists.index', $space);
    }

    public function delete(Space $space, int $id)
    {
        return view('admin.space.contacts_list.delete', [
            'space' => $space,
            'contacts_list' => $space->contactsLists()->findOrFail($id),
        ]);
    }

    public function destroy(Request $request, Space $space)
    {
        $contactsList = $space->contactsLists()->findOrFail($request->get('contacts_lists_id'));
        $contactsList->delete();

        return redirect()->route('admin.spaces.contacts_lists.index', $space);
    }
}
