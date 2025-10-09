<?php

namespace App\Http\Controllers\Api\Account;

use App\AccountFile;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function show(string $uuid, string $name)
    {
        $file = AccountFile::findOrFail($uuid);

        if ($file->name != $name) {
            abort(404);
        }

        return Storage::download($file->path);
    }

    public function upload(Request $request, string $uuid)
    {
        $file = AccountFile::findOrFail($uuid);

        if (!empty($file->name)) {
            abort(404);
        }

        $request->validate([
            'file' => 'required|file|mimetypes:' . $file->content_type
        ]);

        $uploadedFile = $request->file('file');
        $name = Str::random(8) . '_' . $uploadedFile->getClientOriginalName();

        if ($uploadedFile->storeAs('files', $name)) {
            $file->name = $name;
            $file->size = $uploadedFile->getSize();
            $file->uploaded_at = Carbon::now();
            $file->save();

            return $file;
        }

        abort(503);
    }
}
