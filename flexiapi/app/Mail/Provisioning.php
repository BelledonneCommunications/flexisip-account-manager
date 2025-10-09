<?php
/*
    Flexisip Account Manager is a set of tools to manage SIP accounts.
    Copyright (C) 2025 Belledonne Communications SARL, All rights reserved.

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

use App\Account;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Provisioning extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Account $account
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Welcome to :space: Start using your account today', ['space' => $this->account->space->name,]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: view()->exists('mails.provisioning_custom')
                ? 'mails.provisioning_custom'
                : 'mails.provisioning',
        );
    }
}
