<?php

namespace App\Http\Controllers\Account;

use App\AccountFile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function show(string $uuid, string $name)
    {
        $file = AccountFile::findOrFail($uuid);

        if ($file->name != $name) {
            abort(404);
        }

        return Storage::get($file->path);
    }

    public function download(string $uuid, string $name)
    {
        $file = AccountFile::findOrFail($uuid);

        if ($file->name != $name) {
            abort(404);
        }

        return Storage::download($file->path);
    }
}
