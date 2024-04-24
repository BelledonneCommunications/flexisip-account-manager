<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Rules\Vcard;
use App\VcardStorage;
use Illuminate\Http\Request;

use Sabre\VObject;

class VcardsStorageController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->vcardsStorage()->get()->keyBy('uuid');
    }

    public function show(Request $request, string $uuid)
    {
        return $request->user()->vcardsStorage()->where('uuid', $uuid)->firstOrFail();
    }

    public function store(Request $request)
    {
        $request->validate([
            'vcard' => ['required', new Vcard()]
        ]);

        $vcardo = VObject\Reader::read($request->get('vcard'));

        if ($request->user()->vcardsStorage()->where('uuid', $vcardo->UID)->first()) {
            abort(409, 'Vcard already exists');
        }

        $vcard = new VcardStorage();
        $vcard->account_id = $request->user()->id;
        $vcard->uuid = $vcardo->UID;
        $vcard->vcard = preg_replace('/\r\n?/', "\n", $vcardo->serialize());
        $vcard->save();

        return $vcard->vcard;
    }

    public function update(Request $request, string $uuid)
    {
        $request->validate([
            'vcard' => ['required', new Vcard()]
        ]);

        $vcardo = VObject\Reader::read($request->get('vcard'));

        if ($vcardo->UID != $uuid) {
            abort(422, 'UUID should be the same');
        }

        $vcard = $request->user()->vcardsStorage()->where('uuid', $uuid)->firstOrFail();
        $vcard->vcard = preg_replace('/\r\n?/', "\n", $vcardo->serialize());
        $vcard->save();

        return $vcard->vcard;
    }

    public function destroy(Request $request, string $uuid)
    {
        $vcard = $request->user()->vcardsStorage()->where('uuid', $uuid)->firstOrFail();

        return $vcard->delete();
    }
}
