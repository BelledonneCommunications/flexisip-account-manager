<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Account;

class ChangedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function build()
    {
        return $this->view('mails.changed_email')
                    ->text('mails.changed_email_text');
    }
}
