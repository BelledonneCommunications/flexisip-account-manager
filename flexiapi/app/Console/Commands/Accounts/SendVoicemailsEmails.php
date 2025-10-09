<?php

namespace App\Console\Commands\Accounts;

use App\AccountFile;
use App\Mail\Voicemail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendVoicemailsEmails extends Command
{
    protected $signature = 'accounts:send-voicemails-emails {--tryout}';
    protected $description = 'Send the voicemail emails';

    public function handle()
    {
        $voicemails = AccountFile::whereNotNull('uploaded_at')
            ->whereNull('sent_by_mail_at')
            ->where('sending_by_mail_tryouts', '<', is_int($this->option('tryout'))
                ? $this->option('tryout')
                : 3)
            ->get();

        foreach ($voicemails as $voicemail) {
            $voicemail->sending_by_mail_at = Carbon::now();
            $voicemail->save();

            if (Mail::to(users: $voicemail->account)->send(new Voicemail($voicemail))) {
                $voicemail->sent_by_mail_at = Carbon::now();
                $this->info('Voicemail sent to ' . $voicemail->account->identifier);
            } else {
                $voicemail->sending_by_mail_tryouts++;
                $this->info('Error sending voicemail to ' . $voicemail->account->identifier);
            }

            $voicemail->save();
        }
    }
}
