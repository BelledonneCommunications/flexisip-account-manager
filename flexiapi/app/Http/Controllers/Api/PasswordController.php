<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Helpers\Utils;
use App\Mail\ConfirmedRegistration;

class PasswordController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'algorithm' => 'required|in:SHA-256,MD5',
            'password' => 'required',
        ]);

        $account = $request->user();
        $account->activated = true;
        $account->save();

        $algorithm = $request->get('algorithm');

        if ($account->passwords()->count() > 0) {
            $request->validate(['old_password' => 'required']);

            foreach ($account->passwords as $password) {
                if (hash_equals(
                    $password->password,
                    Utils::bchash($account->username, $account->domain, $request->get('old_password'), $password->algorithm)
                )) {
                    $account->updatePassword($request->get('password'), $algorithm);
                    return response()->json();
                }
            }

            return response()->json(['errors' => ['old_password' => 'Incorrect old password']], 422);
        } else {
            $account->updatePassword($request->get('password'), $algorithm);

            if (!empty($account->email)) {
                Mail::to($account)->send(new ConfirmedRegistration($account));
            }
        }
    }
}
