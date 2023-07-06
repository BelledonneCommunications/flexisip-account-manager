<?php

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
        return ContactsList::where('id', $contactsListId)
            ->firstOrFail();
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

        $contactsList = ContactsList::where('id', $contactsListId)
            ->firstOrFail();
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
