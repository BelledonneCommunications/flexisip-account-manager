<?php

namespace App\Http\Controllers\Admin\Space;

use App\Account;
use App\Http\Controllers\Controller;
use App\Space;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VoicemailController extends Controller
{
    public function show(Space $space)
    {
        return view('admin.space.voicemail.show', ['space' => $space]);
    }

    public function enable(Request $request, Space $space)
    {
        if ($space->voicemailEnableable()) {
            $account = new Account;
            $account->username = Space::VOICEMAIL_USERNAME;
            $account->activated = true;
            $account->domain = $space->domain;
            $account->ip_address = $request->ip();
            $account->created_at = Carbon::now();
            $account->user_agent = $request->space->name;
            $account->save();

            $space->voicemail_account_id = $account->id;
            $space->save();
        }

        return redirect()->route('admin.spaces.voicemail.show', $space->domain);
    }
}
