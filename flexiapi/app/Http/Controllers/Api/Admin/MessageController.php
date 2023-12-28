<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2023 Belledonne Communications SARL, All rights reserved.

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

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'to' => 'required',
            'body' => 'required'
        ]);

        $returnedLines = [];

        $loop = \React\EventLoop\Loop::get();
        $connector = new \React\Socket\UnixConnector($loop);

        $connector->connect('unix://'.config('app.linphone_daemon_unix_pipe'))
            ->then(function (\React\Socket\Connection $connection) use ($request, &$returnedLines) {
                $connection->on('data', function ($message) use ($connection, &$returnedLines) {
                    foreach (preg_split("/\r\n|\n|\r/", $message) as $line) {
                        if (!empty($line) && false !== ($matches = explode(':', $line, 2))) {
                            $returnedLines["{$matches[0]}"] = trim($matches[1]);
                        }
                    }

                    $connection->close();
                });

                $connection->write("message sip:".$request->get('to')." ".$request->get('body')."\n");
            }, function (\Exception $e) {
                Log::error($e->getMessage());
            });

        $loop->run();

        if (!array_key_exists('Status', $returnedLines)) {
            throw ValidationException::withMessages(["The internal socket cannot be requested properly"]);
        }

        if ($returnedLines['Status'] == 'Error') {
            throw ValidationException::withMessages([$returnedLines['Reason']]);
        }

        if ($returnedLines['Status'] == 'Ok') {
            return response()->json(['id' => $returnedLines['Id']]);
        }
    }
}
