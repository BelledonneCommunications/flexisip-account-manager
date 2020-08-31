<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Account;

class PasswordAuthentication extends Mailable
{
    use Queueable, SerializesModels;

    private $_account;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Account $account)
    {
        $this->_account = $account;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.authentication')
                    ->text('mails.authentication_text')
                    ->with([
                        'link' => route('account.authenticate.email_confirm', [$this->_account->confirmation_key])
                    ]);
    }
}
