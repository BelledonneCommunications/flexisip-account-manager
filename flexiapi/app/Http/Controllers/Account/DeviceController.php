<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2020 Belledonne Communications SARL, All rights reserved.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Libraries\FlexisipRedisConnector;

class DeviceController extends Controller
{
    public function delete(Request $request, string $uuid)
    {
        $connector = new FlexisipRedisConnector;

        return view(
            'account.device.delete',
            [
                'account' => $request->user(),
                'device' => $connector->getDevices($request->user()->identifier)
                    ->where('uuid', $uuid)->first()
            ]
        );
    }

    public function destroy(Request $request)
    {
        $connector = new FlexisipRedisConnector;
        $connector->deleteDevice($request->user()->identifier, $request->get('uuid'));

        return redirect()->route('account.device.index');
    }
}
