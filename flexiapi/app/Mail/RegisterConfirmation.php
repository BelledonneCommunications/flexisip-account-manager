<?php

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
                        'link' => route('account.authenticate_email_confirm', [$this->_account->confirmation_key])
                    ]);
    }
}
