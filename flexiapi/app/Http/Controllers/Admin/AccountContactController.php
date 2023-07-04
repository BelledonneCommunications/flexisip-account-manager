<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2021 Belledonne Communications SARL, All rights reserved.

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

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Account;

class AccountContactController extends Controller
{
    public function create(int $id)
    {
        $account = Account::findOrFail($id);

        return view('admin.account.contact.create', [
            'account' => $account
        ]);
    }

    public function store(Request $request, int $id)
    {
        $account = Account::findOrFail($id);
        $contact = Account::sip($request->get('sip'))->first();

        if (!$contact) {
            $request->session()->flash('error', 'The contact SIP address doesn\'t exists');

            return redirect()->route('admin.account.contact.create', $account);
        }

        $account->contacts()->detach($contact->id);
        $account->contacts()->attach($contact->id);

        $request->session()->flash('success', 'Contact successfully added');

        Log::channel('events')->info('Web Admin: Account contact added', ['id' => $account->identifier, 'contact' => $contact->identifier]);

        return redirect()->route('admin.account.edit', $account);
    }

    public function delete(int $id, int $contactId)
    {
        $account = Account::findOrFail($id);
        $contact = $account->contacts()->where('id', $contactId)->firstOrFail();

        return view('admin.account.contact.delete', [
            'account' => $account,
            'contact' => $contact
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $account = Account::findOrFail($id);
        $contact = $account->contacts()->where('id', $request->get('contact_id'))->firstOrFail();

        $account->contacts()->detach($contact->id);

        $request->session()->flash('success', 'Type successfully removed');
        Log::channel('events')->info('Web Admin: Account contact removed', ['id' => $account->identifier, 'contact' => $contact->identifier]);

        return redirect()->route('admin.account.edit', $account);
    }
}
