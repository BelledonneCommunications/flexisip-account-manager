<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Libraries\FlexisipConnector;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $connector = new FlexisipConnector;

        return view('account.devices.index',
            ['devices' => $connector->getDevices($request->user()->identifier)
                                    ->keyBy('uuid')
            ]);
    }

    public function delete(Request $request, string $uuid)
    {
        $connector = new FlexisipConnector;

        return view('account.devices.delete',
            ['device' => $connector->getDevices($request->user()->identifier)
                                   ->keyBy('uuid')
                                   ->where('uuid', $uuid)
            ]);
    }

    public function destroy(string $uuid)
    {
        $connector = new FlexisipConnector;
        $connector->deleteDevice($request->user()->identifier, $uuid);

        return redirect()->route('account.device.index');
    }
}
