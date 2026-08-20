<?php

namespace App\Http\Controllers\Admin\Space;

use App\PasswordAlgorithm;
use App\Rules\Domain;
use App\Space;
use App\Http\Controllers\Controller;
use App\SpaceDigestAuthenticationConfiguration;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class DigestController extends Controller
{
    public function show(Space $space)
    {
        return view('admin.space.digest.show', [
            'space' => $space,
            'digestAuthenticationConfiguration' => $space->digestAuthenticationConfiguration()->firstOrNew()
        ]);
    }

    public function store(Request $request, Space $space)
    {
        $request->validate([
            'realm' => ['nullable', new Domain],
            'default_password_algorithm' => [new Enum(PasswordAlgorithm::class)]
        ]);

        $digestAuthenticationConfiguration = $space->digestAuthenticationConfiguration ?: new SpaceDigestAuthenticationConfiguration;
        $digestAuthenticationConfiguration->space_id = $space->id;
        if ($space->accounts()->count() == 0) {
            $digestAuthenticationConfiguration->realm = $request->input('realm');
        }
        $digestAuthenticationConfiguration->default_password_algorithm = $request->input('default_password_algorithm');
        $digestAuthenticationConfiguration->save();

        return redirect()->route('admin.spaces.integration', $space->id);
    }
}
