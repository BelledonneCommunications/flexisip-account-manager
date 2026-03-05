<?php

namespace App\Http\Controllers\Account;

use App\AccountFile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use \App\Http\Controllers\Admin\Account\FileController as AdminFileController;

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

    public function delete(Request $request, string $fileId)
    {
        return (new AdminFileController)->delete($request->user()->id, $fileId);
    }

    public function destroy(Request $request, string $fileId)
    {
        return (new AdminFileController)->destroy($request, $request->user()->id, $fileId);
    }
}
