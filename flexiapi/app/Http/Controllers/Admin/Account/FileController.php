<?php

namespace App\Http\Controllers\Admin\Account;

use App\Account;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function delete(int $accountId, string $fileId)
    {
        $account = Account::findOrFail($accountId);
        $file = $account->files()->where('id', $fileId)->firstOrFail();

        return view('account.file.delete', [
            'account' => $account,
            'file' => $file
        ]);
    }

    public function destroy(Request $request, int $accountId, string $fileId)
    {
        $account = Account::findOrFail($accountId);
        $accountFile = $account->files()
                        ->where('id', $fileId)
                        ->firstOrFail();
        $accountFile->delete();

        return $request->user()->admin
            ? redirect()->route('admin.account.telephony.show', $account)->withFragment('#files')
            : redirect()->route('account.telephony')->withFragment('#files');
    }
}
