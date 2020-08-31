<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

use App\Account;

class AccountController extends Controller
{
    public function home(Request $request)
    {
        if ($request->user()) {
            return redirect()->route('account.panel');
        }

        return view('account.home', [
            'count' => Account::count()
        ]);
    }

    public function panel(Request $request)
    {
        return view('account.panel', [
            'account' => $request->user()
        ]);
    }

    public function terms(Request $request)
    {
        return view('account.terms');
    }

    public function delete(Request $request)
    {
        return view('account.delete', [
            'account' => $request->user()
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['identifier' => 'required|same:identifier_confirm']);

        Auth::logout();
        $request->user()->delete();

        return redirect()->route('account.login');
    }
}
