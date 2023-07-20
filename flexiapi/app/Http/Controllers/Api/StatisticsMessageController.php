<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\StatisticsMessage;
use App\StatisticsMessageDevice;
use Illuminate\Http\Request;

class StatisticsMessageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|string|max:64',
            'from' => 'required|string|max:256',
            'sent_at' => 'required|iso_date',
            'encrypted' => 'required|boolean',
            'conference_id' => 'string|nullable',
        ]);

        $statisticsMessage = new StatisticsMessage;
        $statisticsMessage->id = $request->get('id');
        $statisticsMessage->from = $request->get('from');
        $statisticsMessage->sent_at = $request->get('sent_at');
        $statisticsMessage->encrypted = $request->get('encrypted');
        //$statisticsMessage->conference_id = $request->get('conference_id');

        try {
            return $statisticsMessage->saveOrFail();
        } catch (\Throwable $th) {
            abort(422);
        }
    }

    public function storeDevice(Request $request, string $messageId, string $to, string $deviceId)
    {
        $request->validate([
            // We don't validate the message_id to avoid a specific DB request, the foreign key constraint is taking care of it
            'last_status' => 'required|integer',
            'received_at' => 'required|iso_date'
        ]);

        return StatisticsMessageDevice::updateOrCreate(
            ['message_id' => $messageId, 'to' => $to, 'device_id' => $deviceId],
            ['last_status' => $request->get('last_status'), 'received_at' => $request->get('received_at')]
        );
    }
}
