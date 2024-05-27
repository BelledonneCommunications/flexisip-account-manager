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

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Libraries\FlexisipConnector;

class AccountDeviceController extends Controller
{
    public function index(int $accountId)
    {
        $connector = new FlexisipConnector;
        $account = Account::findOrFail($accountId);

        return view(
            'admin.account.device.index',
            [
                'account' => $account,
                'devices' => $connector->getDevices($account->identifier)
            ]
        );
    }

    public function delete(int $accountId, string $uuid)
    {
        $connector = new FlexisipConnector;
        $account = Account::findOrFail($accountId);

        return view(
            'admin.account.device.delete',
            [
                'account' => $account,
                'device' =>  $connector->getDevices($account->identifier)
                    ->where('uuid', $uuid)->first()
            ]
        );
    }

    public function destroy(Request $request, int $accountId)
    {
        $connector = new FlexisipConnector;
        $connector->deleteDevice(Account::findOrFail($accountId)->identifier, $request->get('uuid'));

        return redirect()->route('admin.account.device.index');
    }
}
