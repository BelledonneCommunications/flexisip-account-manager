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
use App\ContactsList;

class AccountContactController extends Controller
{
    public function index(int $accountId)
    {
        $account = Account::findOrFail($accountId);

        return view('admin.account.contact.index', [
            'account' => $account,
            'contacts_lists' => ContactsList::whereNotIn('id', function ($query) use ($accountId) {
                $query->select('contacts_list_id')
                    ->from('account_contacts_list')
                    ->where('account_id', $accountId);
            })->withCount('contacts')->get()
        ]);
    }

    public function create(int $accountId)
    {
        $account = Account::findOrFail($accountId);

        return view('admin.account.contact.create', [
            'account' => $account
        ]);
    }

    public function store(Request $request, int $accountId)
    {
        $request->validate([
            'sip' => 'required',
        ]);

        $account = Account::findOrFail($accountId);
        $contact = Account::sip($request->get('sip'))->first();

        if (!$contact) {
            return redirect()->back()->withErrors([
                'sip' => __("The contact doesn't exists")
            ]);
        }

        $account->contacts()->detach($contact->id);
        $account->contacts()->attach($contact->id);

        Log::channel('events')->info('Web Admin: Account contact added', ['id' => $account->identifier, 'contact' => $contact->identifier]);

        return redirect()->route('admin.account.contact.index', $account);
    }

    public function delete(int $accountId, int $contactId)
    {
        $account = Account::findOrFail($accountId);
        $contact = $account->contacts()->where('id', $contactId)->firstOrFail();

        return view('admin.account.contact.delete', [
            'account' => $account,
            'contact' => $contact
        ]);
    }

    public function destroy(Request $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $contact = $account->contacts()->where('id', $request->get('contact_id'))->firstOrFail();

        $account->contacts()->detach($contact->id);

        Log::channel('events')->info('Web Admin: Account contact removed', ['id' => $account->identifier, 'contact' => $contact->identifier]);

        return redirect()->route('admin.account.contact.index', $account);
    }
}
