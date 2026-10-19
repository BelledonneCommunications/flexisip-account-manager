<?php

namespace App\Http\Controllers\Api\Admin\Account;

use App\Account;
use App\CallForwarding;
use App\Http\Controllers\Controller;
use App\Rules\CallForwardingEnable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CallForwardingController extends Controller
{
    public function index(Request $request, int $accountId)
    {
        return Account::findOrFail($accountId)->callForwardings;
    }

    public function store(Request $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $request->validate([
            'type' => [
                'required',
                'in:always,no_answer',
                Rule::unique('call_forwardings', 'type')->where(fn ($query) => $query->where('account_id', $accountId))
            ],
            'enabled' => ['required', 'boolean', new CallForwardingEnable($request, $account)],
            'voicemail' => ['boolean', 'missing_with:sip_account_id,contact_account_id', 'missing_if:type,no_answer'],
            'sip_account_id' => [
                'missing_with:voicemail,contact_account_id',
                'not_in:' . $account->id,
                Rule::exists('accounts', 'id')->where(function ($query) use ($account) {
                    $query->where('domain', $account->domain);
                }),
            ],
            'contact_account_id' => [
                'missing_with:voicemail,sip_account_id',
                Rule::in(resolveUserContacts($account)->pluck('id')->toArray())
            ],
        ]);

        $callForwarding = new CallForwarding;
        $callForwarding->account_id = $account->id;
        $callForwarding->type = $request->input('type');
        $callForwarding->enabled = $request->input('enabled');

        $callForwarding->contact_account_id = $request->input('contact_account_id');
        $callForwarding->sip_account_id = $request->input('sip_account_id');

        if ($request->input('voicemail') == true) {
            if ($voicemailAccount = $account->space->voicemailAccount) {
                $callForwarding->voicemail_account_id = $voicemailAccount->id;
            } else {
                abort(422, 'The voicemail is not configured in the Space');
            }
        }

        if ($callForwarding->voicemail_account_id == null
            && $callForwarding->sip_account_id == null
            && $callForwarding->contact_account_id == null) {
            abort(422, 'A call forwarding must have a target');
        }

        $callForwarding->save();

        return $callForwarding;
    }

    public function update(Request $request, int $accountId, string $id)
    {
        $account = Account::findOrFail($accountId);
        $callForwarding = $account->callForwardings()->where('id', $id)->firstOrFail();

        $request->validate([
            'type' => [
                'required',
                'in:always,no_answer',
                Rule::unique('call_forwardings', 'type')
                    ->where(fn ($query) => $query->where('account_id', $accountId))
                    ->ignore($callForwarding->id)
            ],
            'enabled' => ['required', 'boolean', new CallForwardingEnable($request, $account)],
            'voicemail' => ['boolean', 'missing_with:sip_account_id,contact_account_id', 'missing_if:type,no_answer'],
            'sip_account_id' => [
                'missing_with:voicemail,contact_account_id',
                'not_in:' . $account->id,
                Rule::exists('accounts', 'id')->where(function ($query) use ($account) {
                    $query->where('domain', $account->domain);
                }),
            ],
            'contact_account_id' => [
                'missing_with:voicemail,sip_account_id',
                Rule::in(resolveUserContacts($account)->pluck('id')->toArray())
            ],
        ]);

        $callForwarding->contact_account_id
            = $callForwarding->sip_account_id
            = $callForwarding->voicemail_account_id
            = null;

        $callForwarding->enabled = $request->input('enabled');

        $callForwarding->contact_account_id = $request->input('contact_account_id');
        $callForwarding->sip_account_id = $request->input('sip_account_id');

        if ($request->input('voicemail') == true) {
            if ($voicemailAccount = $account->space->voicemailAccount) {
                $callForwarding->voicemail_account_id = $voicemailAccount->id;
            } else {
                abort(422, 'The voicemail is not configured in the Space');
            }
        }

        if ($callForwarding->voicemail_account_id == null
            && $callForwarding->sip_account_id == null
            && $callForwarding->contact_account_id == null) {
            abort(422, 'A call forwarding must have a target');
        }

        $callForwarding->save();

        return $callForwarding;
    }

    public function show(Request $request, int $accountId, string $id)
    {
        return Account::findOrFail($accountId)->callForwardings()->where('id', $id)->firstOrFail();
    }

    public function destroy(Request $request, int $accountId, string $id)
    {
        return Account::findOrFail($accountId)->callForwardings()->where('id', $id)->delete();
    }
}
