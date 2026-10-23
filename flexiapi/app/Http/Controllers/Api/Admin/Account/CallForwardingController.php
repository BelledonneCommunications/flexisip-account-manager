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
        $request->validate($this->validationRules($request, $account));

        $callForwarding = new CallForwarding;
        $callForwarding->account_id = $account->id;
        $callForwarding->type = $request->input('type');

        return $this->save($request, $account, $callForwarding);
    }

    public function update(Request $request, int $accountId, string $callForwardingId)
    {
        $account = Account::findOrFail($accountId);
        $callForwarding = $account->callForwardings()->where('id', $callForwardingId)->firstOrFail();
        $request->validate($this->validationRules($request, $account, $callForwarding));

        return $this->save($request, $account, $callForwarding);
    }

    public function show(Request $request, int $accountId, string $callForwardingId)
    {
        return Account::findOrFail($accountId)->callForwardings()->where('id', $callForwardingId)->firstOrFail();
    }

    public function destroy(Request $request, int $accountId, string $callForwardingId)
    {
        return Account::findOrFail($accountId)->callForwardings()->where('id', $callForwardingId)->delete();
    }

    private function validationRules(Request $request, Account $account, ?CallForwarding $callForwarding = null): array
    {
        $uniqueType = Rule::unique('call_forwardings', 'type')
            ->where(fn ($query) => $query->where('account_id', $account->id));

        if ($callForwarding != null) {
            $uniqueType->ignore($callForwarding->id);
        }

        return [
            'type' => [
                'required',
                'in:always,no_answer',
                $uniqueType
            ],
            'enabled' => ['required', 'boolean', new CallForwardingEnable($request, $account)],
            'voicemail' => ['boolean', 'missing_with:sip_account_id', 'missing_if:type,no_answer'],
            'sip_account_id' => [
                'missing_with:voicemail',
                'not_in:' . $account->id,
                Rule::exists('accounts', 'id')->where(function ($query) use ($account) {
                    $query->where('domain', $account->domain);
                }),
            ],
        ];
    }

    private function save(Request $request, Account $account, CallForwarding $callForwarding): CallForwarding
    {
        $callForwarding->enabled = $request->input('enabled');

        $this->applyTargets(
            $callForwarding,
            $account,
            $request->input('sip_account_id'),
            (bool) $request->input('voicemail')
        );

        $callForwarding->save();

        return $callForwarding;
    }

    /**
     * Apply the target of a call forwarding: exactly one of a SIP account
     * of the Space or the voicemail account of the Space.
     */
    private function applyTargets(CallForwarding $callForwarding, Account $account, ?int $sipAccountId, bool $voicemail): void
    {
        $callForwarding->sip_account_id = $sipAccountId;
        $callForwarding->voicemail_account_id = null;

        if ($voicemail) {
            if ($voicemailAccount = $account->space->voicemailAccount) {
                $callForwarding->voicemail_account_id = $voicemailAccount->id;
            } else {
                abort(422, 'The voicemail is not configured in the Space');
            }
        }

        if ($callForwarding->voicemail_account_id == null
            && $callForwarding->sip_account_id == null) {
            abort(422, 'A call forwarding must have a target');
        }
    }
}
