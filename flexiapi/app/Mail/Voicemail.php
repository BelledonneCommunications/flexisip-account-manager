<?php

namespace App\Mail;

use App\AccountFile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class Voicemail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccountFile $accountFile)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->accountFile->account->space->name .
            ': ' .
            __('New voice message from :sipfrom', ['sipfrom' => $this->accountFile->sip_from]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mails.voicemail',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorage($this->accountFile->path)
                ->withMime($this->accountFile->content_type)
        ];
    }
}
