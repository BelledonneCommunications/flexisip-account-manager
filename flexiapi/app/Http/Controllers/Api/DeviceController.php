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

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Libraries\FlexisipConnector;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $connector = new FlexisipConnector;

        return $connector->getDevices($request->user()->identifier)
                         ->keyBy('uuid');
    }

    public function destroy(string $uuid)
    {
        $connector = new FlexisipConnector;

        return $connector->deleteDevice($request->user()->identifier, $uuid);
    }
}
