<?php

namespace App\Http\Controllers\Admin\Account;

use App\Account;
use App\CallForwarding;
use App\Http\Controllers\Controller;
use App\Rules\SipUriFromTheSpace;
use Illuminate\Http\Request;

class CallForwardingController extends Controller
{
    public function update(Request $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);

        $request->validate([
            'status' => 'required|in:disabled,always,no_answer',

            'always.forward_to' => 'required|in:sip_uri,voicemail',
            'always.sip_uri' => $request->input('status') == 'always'
                ? ['nullable', new SipUriFromTheSpace, 'required_if:always.forward_to,sip_uri', 'not_in:' . $account->sipUri]
                : 'nullable',

            'no_answer.sip_uri' => $request->input('status') == 'no_answer'
                ? ['required', new SipUriFromTheSpace, 'not_in:' . $account->sipUri]
                : 'nullable',
        ]);

        $voicemailAccount = $account->space->voicemailAccount;

        if ($voicemailAccount == null) {
            $request->validate([
                'always.forward_to' => 'required|in:sip_uri',
            ]);
        }

        $account->callForwardings()->update(['enabled' => false]);

        if ($request->input('status') == 'always') {
            $alwaysForwarding = $account->callForwardings()->where('type', 'always')->first() ?? new CallForwarding;
            $alwaysForwarding->enabled = true;
            $alwaysForwarding->account_id = $account->id;
            $alwaysForwarding->type = 'always';
            $alwaysForwarding->sip_account_id = $request->input('always')['forward_to'] == 'sip_uri'
                ? Account::sip($request->input('always')['sip_uri'])->first()->id
                : null;
            $alwaysForwarding->voicemail_account_id = $request->input('always')['forward_to'] == 'voicemail'
                ? $voicemailAccount->id
                : null;
            $alwaysForwarding->save();
        }

        if ($request->input('status') == 'no_answer') {
            $noAnswerForwarding = $account->callForwardings()->where('type', 'no_answer')->first() ?? new CallForwarding;
            $noAnswerForwarding->enabled = true;
            $noAnswerForwarding->account_id = $account->id;
            $noAnswerForwarding->type = 'no_answer';
            $noAnswerForwarding->sip_account_id = Account::sip($request->input('no_answer')['sip_uri'])->first()->id;
            $noAnswerForwarding->save();
        }

        return redirect()->back();
    }
}
