<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Account;
use App\Admin;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.account.index', [
            'accounts' => Account::orderBy('creation_time', 'desc')->paginate(30)
        ]);
    }

    public function show(Request $request, $id)
    {
        return view('admin.account.show', [
            'account' => Account::findOrFail($id)
        ]);
    }

    public function activate(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        $account->activated = true;
        $account->save();

        return redirect()->back();
    }

    public function deactivate(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        $account->activated = false;
        $account->save();

        return redirect()->back();
    }

    public function admin(Request $request, $id)
    {
        $account = Account::findOrFail($id);

        $admin = new Admin;
        $admin->account_id = $account->id;
        $admin->save();

        return redirect()->back();
    }

    public function unadmin(Request $request, $id)
    {
        $account = Account::findOrFail($id);

        // An admin cannot remove it's own permission
        if ($account->id == $request->user()->id) abort(403);

        if ($account->admin) $account->admin->delete();

        return redirect()->back();
    }
}
