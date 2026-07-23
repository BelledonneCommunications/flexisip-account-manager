<?php

namespace App\Http\Controllers\Admin\Account;

use App\Account;
use App\CallForwarding;
use App\Http\Controllers\Controller;
use App\Rules\SipUri;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CallForwardingController extends Controller
{
    public function update(Request $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $contactsIds = resolveUserContacts($account)->pluck('id')->toArray();

        $forwardTo = 'required|in:sip_uri,contact,voicemail';

        $request->validate([
            'always.forward_to' => $forwardTo,
            'always.sip_uri' => array_key_exists('enabled', $request->input('always'))
                ? ['nullable', new SipUri, 'required_if:always.forward_to,sip_uri']
                : 'nullable',
            'always.contact_id' => ['required_if:always.forward_to,contact', Rule::in($contactsIds)],

            'no_answer.forward_to' => $forwardTo,
            'no_answer.sip_uri' => array_key_exists('enabled', $request->input('no_answer'))
                ? ['nullable', new SipUri, 'required_if:no_answer.forward_to,sip_uri']
                : 'nullable',
            'no_answer.contact_id' => ['required_if:no_answer.forward_to,contact', Rule::in($contactsIds)],

            'busy.forward_to' => $forwardTo,
            'busy.sip_uri' => array_key_exists('enabled', $request->input('busy'))
                ? ['nullable', new SipUri, 'required_if:busy.forward_to,sip_uri']
                : 'nullable',
            'busy.contact_id' => ['required_if:busy.forward_to,contact', Rule::in($contactsIds)],
        ]);

        $account->callForwardings()->update(['enabled' => false]);

        if (array_key_exists('enabled', $request->input('always'))) {
            $alwaysForwarding = $account->callForwardings()->where('type', 'always')->first() ?? new CallForwarding;
            $alwaysForwarding->enabled = true;
            $alwaysForwarding->account_id = $account->id;
            $alwaysForwarding->type = 'always';
            $alwaysForwarding->forward_to = $request->input('always')['forward_to'];
            $alwaysForwarding->sip_uri = $request->input('always')['sip_uri'];
            $alwaysForwarding->contact_id = $request->input('always')['forward_to'] == 'contact'
                ? $request->input('always')['contact_id']
                : null;
            $alwaysForwarding->save();
        }

        if (array_key_exists('enabled', $request->input('no_answer'))) {
            $noAnswerForwarding = $account->callForwardings()->where('type', 'no_answer')->first() ?? new CallForwarding;
            $noAnswerForwarding->enabled = true;
            $noAnswerForwarding->account_id = $account->id;
            $noAnswerForwarding->type = 'no_answer';
            $noAnswerForwarding->forward_to = $request->input('no_answer')['forward_to'];
            $noAnswerForwarding->sip_uri = $request->input('no_answer')['sip_uri'];
            $noAnswerForwarding->contact_id = $request->input('no_answer')['forward_to'] == 'contact'
                ? $request->input('no_answer')['contact_id']
                : null;
            $noAnswerForwarding->save();
        }

        if (array_key_exists('enabled', $request->input('busy'))) {
            $busyForwarding = $account->callForwardings()->where('type', 'busy')->first() ?? new CallForwarding;
            $busyForwarding->enabled = true;
            $busyForwarding->account_id = $account->id;
            $busyForwarding->type = 'busy';
            $busyForwarding->forward_to = $request->input('busy')['forward_to'];
            $busyForwarding->sip_uri = $request->input('busy')['sip_uri'];
            $busyForwarding->contact_id = $request->input('busy')['forward_to'] == 'contact'
                ? $request->input('busy')['contact_id']
                : null;
            $busyForwarding->save();
        }

        return redirect()->back();
    }
}
