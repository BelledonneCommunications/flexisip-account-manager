<?php

namespace App\Http\Controllers\Api\Admin\Account;

use App\Account;
use App\AccountFile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VoicemailController extends Controller
{
    public function index(Request $request, int $accountId)
    {
        return Account::findOrFail($accountId)->voicemails;
    }

    public function store(Request $request, int $accountId)
    {
        $account = Account::findOrFail($accountId);
        $request->validate([
            'sip_from' => 'nullable|starts_with:sip',
            'content_type' => [
                'required',
                Rule::in(AccountFile::VOICEMAIL_CONTENTTYPES),
            ]
        ]);

        $voicemail = new AccountFile;
        $voicemail->account_id = $account->id;
        $voicemail->sip_from = $request->get('sip_from');
        $voicemail->content_type = $request->get('content_type');
        $voicemail->save();

        $voicemail->append(['upload_url', 'max_upload_size']);

        return $voicemail;
    }

    public function show(Request $request, int $accountId, string $uuid)
    {
        return Account::findOrFail($accountId)->voicemails()->where('id', $uuid)->firstOrFail();
    }

    public function destroy(Request $request, int $accountId, string $uuid)
    {
        return Account::findOrFail($accountId)->voicemails()->where('id', $uuid)->delete();
    }
}
