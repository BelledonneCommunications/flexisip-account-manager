<?php

/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2026 Belledonne Communications SARL, All rights reserved.

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

class StatisticsController extends Controller
{
    public function editCallLogs(Request $request)
    {
        return redirect()->route('account.statistics.show_call_logs', [
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'direction' => $request->input('direction'),
            'account' => $request->user()->id,
            'page' => $request->input('page'),
        ]);
    }

    public function showCallLogs(Request $request)
    {
        return (new \App\Http\Controllers\Admin\Account\StatisticsController)
            ->showCallLogs($request, $request->user()->id, adminView: false);
    }
}
