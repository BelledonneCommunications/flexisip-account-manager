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

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Account;

class RegisterConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    private $_account;

    public function __construct(Account $account)
    {
        $this->_account = $account;
    }

    public function build()
    {
        return $this->view('mails.register_confirmation')
                    ->text('mails.register_confirmation_text')
                    ->with([
                        'link' => route('account.authenticate.email_confirm', [$this->_account->confirmation_key])
                    ]);
    }
}
