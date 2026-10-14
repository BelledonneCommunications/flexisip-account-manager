<?php

namespace App\Http\Controllers\Admin\Account;

use App\Account;
use App\CallForwarding;
use App\Http\Controllers\Controller;
use App\Rules\SipUriFromTheSpace;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CallForwardingController extends Controller
{
    public function update(Request $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $contactsIds = resolveUserContacts($account)->pluck('id')->toArray();

        $request->validate([
            'status' => 'required|in:disabled,always,no_answer',

            'always.forward_to' => 'required|in:sip_uri,contact,voicemail',
            'always.sip_uri' => $request->input('status') == 'always'
                ? ['nullable', new SipUriFromTheSpace, 'required_if:always.forward_to,sip_uri', 'not_in:' . $account->sipUri]
                : 'nullable',
            'always.contact_account_id' => ['required_if:always.forward_to,contact', Rule::in($contactsIds)],

            'no_answer.forward_to' => 'required|in:sip_uri,contact',
            'no_answer.sip_uri' => $request->input('status') == 'no_answer'
                ? ['nullable', new SipUriFromTheSpace, 'required_if:no_answer.forward_to,sip_uri', 'not_in:' . $account->sipUri]
                : 'nullable',
            'no_answer.contact_account_id' => ['required_if:no_answer.forward_to,contact', Rule::in($contactsIds)],
        ]);

        $voicemailAccount = $account->space->voicemailAccount;

        if ($voicemailAccount == null) {
            $request->validate([
                'always.forward_to' => 'required|in:sip_uri,contact',
            ]);
        }

        $account->callForwardings()->update(['enabled' => false]);

        if ($request->input('status') == 'always') {
            $alwaysForwarding = $account->callForwardings()->where('type', 'always')->first() ?? new CallForwarding;
            $alwaysForwarding->enabled = true;
            $alwaysForwarding->account_id = $account->id;
            $alwaysForwarding->type = 'always';
            $alwaysForwarding->sip_account_id = $request->input('always')['forward_to'] == 'sip_uri' && $request->input('always')['sip_uri']
                ? Account::sip($request->input('always')['sip_uri'])->first()->id
                : null;
            $alwaysForwarding->contact_account_id = $request->input('always')['forward_to'] == 'contact' && $request->input('always')['contact_account_id']
                ? $request->input('always')['contact_account_id']
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
            $noAnswerForwarding->sip_account_id = $request->input('no_answer')['forward_to'] == 'sip_uri' && $request->input('no_answer')['sip_uri']
                ? Account::sip($request->input('no_answer')['sip_uri'])->first()->id
                : null;
            $noAnswerForwarding->contact_account_id = $request->input('no_answer')['forward_to'] == 'contact' && $request->input('no_answer')['contact_account_id']
                ? $request->input('no_answer')['contact_account_id']
                : null;
            $noAnswerForwarding->save();
        }

        return redirect()->back();
    }
}
