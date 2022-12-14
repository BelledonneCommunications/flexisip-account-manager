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
    private $pusherPath;
    private $pnProvider;
    private $pnParam;
    private $pnPrid;

    public function __construct(string $pnProvider, string $pnParam, string $pnPrid)
    {
        $this->pusherPath = config('app.flexisip_pusher_path');
        $this->pnProvider = $pnProvider;
        $this->pnParam = $pnParam;
        $this->pnPrid = $pnPrid;
    }

    public function sendToken(string $token)
    {
        $payload = json_encode(['token' => $token]);

        if (!empty($this->pusherPath)) {
            $command = $this->pusherPath
            . " --pn-provider '" . $this->pnProvider . "'"
            . " --pn-param '" . $this->pnParam . "'"
            . " --pn-prid " . $this->pnPrid
            . " --apple-push-type Background"
            . " --customPayload '" . $payload . "'";

            $output = null;
            $retval = null;

            return exec($command, $output, $retval);
        }

        Log::error('Pusher path not configured');
    }
}
