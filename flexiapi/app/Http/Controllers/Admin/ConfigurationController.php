<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Configuration;

class ConfigurationController extends Controller
{
    public function edit(Request $request)
    {
        $configuration = Configuration::first();
        if (!$configuration) $configuration = new Configuration;

        return view('admin.configuration.edit', [
            'configuration' => $configuration
        ]);
    }

    public function update(Request $request)
    {
        $configuration = Configuration::first();
        if (!$configuration) $configuration = new Configuration;

        $configuration->copyright = $request->get('copyright');
        $configuration->intro_registration = $request->get('intro_registration');
        $configuration->custom_theme = $request->input('custom_theme', false);
        $configuration->save();

        return redirect()->route('admin.configuration.update');
    }
}
