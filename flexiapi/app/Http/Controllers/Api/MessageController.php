<?php

namespace App\Http\Controllers\Api;

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
