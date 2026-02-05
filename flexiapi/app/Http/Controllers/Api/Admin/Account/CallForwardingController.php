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
                'in:always,away,busy',
                Rule::unique('call_forwardings', 'type')->where(fn($query) => $query->where('account_id', $accountId))
            ],
            'forward_to' => 'required|in:sip_uri,contact,voicemail',
            'sip_uri' => 'nullable|starts_with:sip:|required_if:forward_to,sip_uri',
            'enabled' => ['required', 'boolean', new CallForwardingEnable($request, $account)],
            'contact_id' => ['required_if:forward_to,contact', Rule::in(resolveUserContacts($account)->pluck('id')->toArray())],
        ]);

        $callForwarding = new CallForwarding;
        $callForwarding->account_id = $account->id;
        $callForwarding->type = $request->get('type');
        $callForwarding->forward_to = $request->get('forward_to');
        $callForwarding->sip_uri = $request->get('sip_uri');
        $callForwarding->enabled = $request->get('enabled');
        $callForwarding->contact_id = $request->get('contact_id');
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
                'in:always,away,busy',
                Rule::unique('call_forwardings', 'type')
                    ->where(fn($query) => $query->where('account_id', $accountId))
                    ->ignore($callForwarding->id)
            ],
            'forward_to' => 'required|in:sip_uri',
            'sip_uri' => 'required|starts_with:sip',
            'enabled' => ['required', 'boolean', new CallForwardingEnable($request, $account)]
        ]);

        $callForwarding->forward_to = $request->get('forward_to');
        $callForwarding->sip_uri = $request->get('sip_uri');
        $callForwarding->enabled = $request->get('enabled');
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
