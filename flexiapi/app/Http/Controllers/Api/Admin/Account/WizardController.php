<?php

namespace App\Http\Controllers\Api\Admin\Account;

use App\Http\Controllers\Controller;
use App\Rules\SipUri;
use App\Wizard;
use App\Account;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WizardController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'provisioning_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'sip' => ['required', 'string', new SipUri],
            'linphone_action' => ['nullable', 'string', Rule::in(Wizard::LINPHONE_ACTION)],
            'linphone_use_sips' => ['nullable', 'boolean'],
        ]);

        $wizard = $this->createWizard(
            accountId: $request->user()->id,
            provisioningAccountId: $request->provisioning_account_id,
            sip: $request->sip,
            linphoneAction: $request->linphone_action,
            linphoneUseSips: $request->linphone_use_sips,
        );

        return $wizard;
    }

    public function createForAccount(Account $account): Wizard
    {
        $wizard = $this->createWizard(
            accountId: $account->id,
            provisioningAccountId: $account->id
        );
        return $wizard;
    }

    private function createWizard(
        int $accountId,
        ?int $provisioningAccountId = null,
        ?string $sip = null,
        ?string $linphoneAction = null,
        bool $linphoneUseSips = false
    ): Wizard {
        $wizard = new Wizard();
        $wizard->token = Str::random(8);
        $wizard->account_id = $accountId;
        $wizard->provisioning_account_id = $provisioningAccountId;
        $wizard->sip = $sip;
        $wizard->linphone_action = $linphoneAction;
        $wizard->linphone_use_sips = $linphoneUseSips;
        $wizard->save();

        return $wizard;
    }
}
