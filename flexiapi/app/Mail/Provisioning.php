<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Account;

class Provisioning extends Mailable
{
    use Queueable, SerializesModels;

    private $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function build()
    {
        return $this->view(view()->exists('mails.provisioning_custom')
                ? 'mails.provisioning_custom'
                : 'mails.provisioning')
            ->text(view()->exists('mails.provisioning_text_custom')
                ? 'mails.provisioning_text_custom'
                : 'mails.provisioning_text')
            ->with([
                'provisioning_link' => route('provisioning.provision', [
                    'provisioning_token' => $this->account->provisioning_token,
                    'reset_password' => true
                ]),
                'provisioning_qrcode' => route('provisioning.qrcode', [
                    'provisioning_token' => $this->account->provisioning_token,
                    'reset_password' => true
                ])
            ]);
    }
}
