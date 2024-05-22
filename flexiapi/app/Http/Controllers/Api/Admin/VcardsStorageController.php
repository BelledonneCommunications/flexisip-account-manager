<?php

namespace App\Http\Controllers\Api\Admin;

use App\Account;
use App\Http\Controllers\Controller;
use App\Rules\Vcard;
use App\VcardStorage;
use Illuminate\Http\Request;

use Sabre\VObject;

class VcardsStorageController extends Controller
{
    public function index(int $accountId)
    {
        return Account::findOrFail($accountId)->vcardsStorage()->get()->keyBy('uuid');
    }

    public function show(int $accountId, string $uuid)
    {
        return Account::findOrFail($accountId)->vcardsStorage()->where('uuid', $uuid)->firstOrFail();
    }

    public function store(Request $request, int $accountId)
    {
        $request->validate([
            'vcard' => ['required', new Vcard()]
        ]);

        $vcardo = VObject\Reader::read($request->get('vcard'));

        if (Account::findOrFail($accountId)->vcardsStorage()->where('uuid', $vcardo->UID)->first()) {
            abort(409, 'Vcard already exists');
        }

        $vcard = new VcardStorage();
        $vcard->account_id = $accountId;
        $vcard->uuid = $vcardo->UID;
        $vcard->vcard = preg_replace('/\r\n?/', "\n", $vcardo->serialize());
        $vcard->save();

        return $vcard->vcard;
    }

    public function update(Request $request, int $accountId, string $uuid)
    {
        $request->validate([
            'vcard' => ['required', new Vcard()]
        ]);

        $vcardo = VObject\Reader::read($request->get('vcard'));

        if ($vcardo->UID != $uuid) {
            abort(422, 'UUID should be the same');
        }

        $vcard = Account::findOrFail($accountId)->vcardsStorage()->where('uuid', $uuid)->firstOrFail();
        $vcard->vcard = preg_replace('/\r\n?/', "\n", $vcardo->serialize());
        $vcard->save();

        return $vcard->vcard;
    }

    public function destroy(int $accountId, string $uuid)
    {
        $vcard = Account::findOrFail($accountId)->vcardsStorage()->where('uuid', $uuid)->firstOrFail();

        return $vcard->delete();
    }
}
