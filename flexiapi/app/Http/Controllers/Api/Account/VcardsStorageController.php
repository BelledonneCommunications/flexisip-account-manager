<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Api\Admin\VcardsStorageController as AdminVcardsStorageController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VcardsStorageController extends Controller
{
    public function index(Request $request)
    {
        return (new AdminVcardsStorageController)->index($request->user()->id);
    }

    public function show(Request $request, string $uuid)
    {
        return (new AdminVcardsStorageController)->show($request->user()->id, $uuid);
    }

    public function store(Request $request)
    {
        return (new AdminVcardsStorageController)->store($request, $request->user()->id);
    }

    public function update(Request $request, string $uuid)
    {
        return (new AdminVcardsStorageController)->update($request, $request->user()->id, $uuid);
    }

    public function destroy(Request $request, string $uuid)
    {
        return (new AdminVcardsStorageController)->destroy($request->user()->id, $uuid);
    }
}
