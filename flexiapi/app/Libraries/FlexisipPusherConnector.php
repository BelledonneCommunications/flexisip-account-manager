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

namespace App\Libraries;

use Illuminate\Support\Facades\Log;

class FlexisipPusherConnector
{
    private ?string $pusherPath = null;
    private ?string $pnProvider = null;
    private ?string $pnParam = null;
    private ?string $pnPrid = null;
    private ?string $pusherFirebaseKey = null;

    public function __construct(string $pnProvider, string $pnParam, string $pnPrid)
    {
        $this->pusherPath = config('app.flexisip_pusher_path');
        $this->pnProvider = $pnProvider;
        $this->pnParam = $pnParam;
        $this->pnPrid = $pnPrid;

        if ($this->pnProvider == 'fcm' && config('app.flexisip_pusher_firebase_keysmap') == null) {
            Log::error('Firebase pusher keysmap not configured');
        }

        $firebaseKeysmap = explode(' ', config('app.flexisip_pusher_firebase_keysmap'));

        if (count($firebaseKeysmap) > 0) {
            $pusherFirebaseKeysmap = [];

            foreach ($firebaseKeysmap as $map) {
                if (str_contains($map, ':')) {
                    //We put the explode limit to 2 to also support legacy firebase keys format (number:alphanum:alphanumsymb)
                    list($id, $value) = explode(':', $map, 2);
                    $pusherFirebaseKeysmap[$id] = $value;
                }
            }

            if (array_key_exists($pnParam, $pusherFirebaseKeysmap)) {
                $this->pusherFirebaseKey = $pusherFirebaseKeysmap[$pnParam];
            }
        }
    }

    public function sendToken(string $token)
    {
        $payload = json_encode(['token' => $token]);

        if (!empty($this->pusherPath)) {
            $command = $this->pusherPath
                . " --pn-provider '" . $this->pnProvider . "'"
                . " --pn-param '" . $this->pnParam . "'"
                . " --pn-prid " . $this->pnPrid
                . " --customPayload '" . $payload . "'";

            if (in_array($this->pnProvider, ['apns', 'apns.dev'])) {
                $command .= " --apple-push-type Background";
            }

            if ($this->pusherFirebaseKey) {
                $command .= " --key " . $this->pusherFirebaseKey;
            }

            $output = null;
            $retval = null;

            return exec($command, $output, $retval);
        }

        Log::error('Pusher path not configured');
    }
}
