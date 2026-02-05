<?php

namespace App\Http\Controllers\Admin\Account;

use App\Account;
use App\CallForwarding;
use App\Http\Controllers\Controller;
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
            'always.sip_uri' => 'nullable|starts_with:sip:|required_if:always.forward_to,sip_uri',
            'always.contact_id' => ['required_if:always.forward_to,contact', Rule::in($contactsIds)],

            'away.forward_to' => $forwardTo,
            'away.sip_uri' => 'nullable|starts_with:sip:|required_if:away.forward_to,sip_uri',
            'away.contact_id' => ['required_if:away.forward_to,contact', Rule::in($contactsIds)],

            'busy.forward_to' => $forwardTo,
            'busy.sip_uri' => 'nullable|starts_with:sip:|required_if:busy.forward_to,sip_uri',
            'busy.contact_id' => ['required_if:busy.forward_to,contact', Rule::in($contactsIds)],
        ]);

        $account->callForwardings()->update(['enabled' => false]);

        if (array_key_exists('enabled', $request->get('always'))) {
            $alwaysForwarding = $account->callForwardings()->where('type', 'always')->first() ?? new CallForwarding;
            $alwaysForwarding->enabled = true;
            $alwaysForwarding->account_id = $account->id;
            $alwaysForwarding->type = 'always';
            $alwaysForwarding->forward_to = $request->get('always')['forward_to'];
            $alwaysForwarding->sip_uri = $request->get('always')['sip_uri'];
            $alwaysForwarding->contact_id = $request->get('always')['forward_to'] == 'contact'
                ? $request->get('always')['contact_id']
                : null;
            $alwaysForwarding->save();
        }

        if (array_key_exists('enabled', $request->get('away'))) {
            $awayForwarding = $account->callForwardings()->where('type', 'away')->first() ?? new CallForwarding;
            $awayForwarding->enabled = true;
            $awayForwarding->account_id = $account->id;
            $awayForwarding->type = 'away';
            $awayForwarding->forward_to = $request->get('away')['forward_to'];
            $awayForwarding->sip_uri = $request->get('away')['sip_uri'];
            $awayForwarding->contact_id = $request->get('away')['forward_to'] == 'contact'
                ? $request->get('away')['contact_id']
                : null;
            $awayForwarding->save();
        }

        if (array_key_exists('enabled', $request->get('busy'))) {
            $busyForwarding = $account->callForwardings()->where('type', 'busy')->first() ?? new CallForwarding;
            $busyForwarding->enabled = true;
            $busyForwarding->account_id = $account->id;
            $busyForwarding->type = 'busy';
            $busyForwarding->forward_to = $request->get('busy')['forward_to'];
            $busyForwarding->sip_uri = $request->get('busy')['sip_uri'];
            $busyForwarding->contact_id = $request->get('busy')['forward_to'] == 'contact'
                ? $request->get('busy')['contact_id']
                : null;
            $busyForwarding->save();
        }

        return redirect()->route('admin.account.telephony.show', $account);
    }
}
