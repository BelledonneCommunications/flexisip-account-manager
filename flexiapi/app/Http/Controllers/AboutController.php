<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function about(Request $request)
    {
        return view('about');
    }

    public function thirdPartyComponents(Request $request)
    {
        $cleanedContent = preg_replace([
            '/^([-=]+\n)/m', // Removing the --- and === titles
            '/\# The MIT License \(MIT\)/m' // Removing a broken case
        ], [
            '',
            'The MIT License (MIT)'
        ], file_get_contents(base_path('licenses.md')));

        return view('third_party_components', [
            'documentation' => markdownDocumentation($cleanedContent)
        ]);
    }
}
