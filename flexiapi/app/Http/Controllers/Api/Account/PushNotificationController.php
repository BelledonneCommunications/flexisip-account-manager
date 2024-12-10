<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Libraries\FlexisipPusherConnector;
use App\Rules\PnParam;
use App\Rules\PnPrid;
use App\Rules\PnProvider;
use App\Rules\CallId;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PushNotificationController extends Controller
{
    public function push(Request $request)
    {
        $request->validate([
            'pn_provider' => ['required', new PnProvider],
            'pn_param' => [new PnParam],
            'pn_prid' => [new PnPrid],
            'type' => ['required', Rule::in(array_keys(FlexisipPusherConnector::$apnsTypes))],
            'call_id' => [new CallId],
        ]);

        $fp = new FlexisipPusherConnector($request->get('pn_provider'), $request->get('pn_param'), $request->get('pn_prid'));

        if ($fp->send(callId: $request->get('call_id'), type: $request->get('type'))) {
            Log::channel('events')->info('API: Push notification sent', [
                'call_id' => $request->get('call_id'),
                'type' => $request->get('type')
            ]);
            return;
        }

        abort(503, "Push notification not sent");
    }
}
