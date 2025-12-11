<?php

namespace App\Http\Controllers\Api\Account;

use App\AccountFile;
use App\Http\Controllers\Controller;
use App\Rules\AudioMime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function upload(Request $request, string $uuid)
    {
        $file = AccountFile::findOrFail($uuid);

        if (!empty($file->name)) {
            abort(404);
        }

        $request->validate(['file' => 'required|file']);

        if ($file->isVoicemailAudio()) {
            $request->validate(['file' => [new AudioMime($file)]]);
        }

        $uploadedFile = $request->file('file');
        $name = Str::random(8) . '_' . $uploadedFile->getClientOriginalName();

        if ($uploadedFile->storeAs(AccountFile::FILES_PATH, $name)) {
            $file->name = $name;
            $file->size = $uploadedFile->getSize();
            $file->uploaded_at = Carbon::now();
            $file->save();

            return $file;
        }

        abort(503);
    }
}
