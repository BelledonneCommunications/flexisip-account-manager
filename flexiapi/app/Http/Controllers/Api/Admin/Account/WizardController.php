<?php

namespace App\Http\Controllers\Api\Admin\Account;

use App\Http\Controllers\Controller;
use App\Rules\SipUri;
use App\Wizard;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

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

        $wizard = new Wizard;
        $wizard->account_id = $request->user()->id;
        $wizard->provisioning_account_id = $request->provisioning_account_id;
        $wizard->sip = $request->sip;
        $wizard->linphone_action = $request->linphone_action;
        $wizard->linphone_use_sips = $request->linphone_use_sips;
        $wizard->save();

        return $wizard;
    }
}
