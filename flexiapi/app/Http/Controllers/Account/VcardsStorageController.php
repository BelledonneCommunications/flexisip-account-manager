<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VcardsStorageController extends Controller
{
    public function index(Request $request)
    {
        //if ($this->vcardRequested($request)) {
            $vcards = '';

            foreach ($request->user()->vcardsStorage()->get() as $vcard) {
                $vcards .= $vcard->vcard . "\n";
            }

            return $vcards;
        /*}

        abort(404);*/
    }

    public function show(Request $request, string $uuid)
    {
        return /*($this->vcardRequested($request))
            ?*/ $request->user()->vcardsStorage()->where('uuid', $uuid)->firstOrFail()->vcard
            /*: abort(404)*/;
    }

    /*private function vcardRequested(Request $request): bool
    {
        return $request->hasHeader('content-type') == 'text/vcard'
            && $request->hasHeader('accept') == 'text/vcard';
    }*/
}
