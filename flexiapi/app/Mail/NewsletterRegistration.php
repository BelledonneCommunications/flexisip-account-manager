<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Account;

class NewsletterRegistration extends Mailable
{
    use Queueable, SerializesModels;

    private $_account;

    public function __construct(Account $account)
    {
        $this->_account = $account;
    }

    public function build()
    {
        return $this->view('mails.newsletter_registration')
                    ->text('mails.newsletter_registration_text')
                    ->with(['account' => $this->_account]);
    }
}
