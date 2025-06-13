<?php

namespace App\Mail;

use App\Space;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExpiringSpace extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Space $space
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Your space :space is expiring in :count days', [
                'space' => $this->space->name,
                'count' => $this->space->daysLeft,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mails.expiring_space',
        );
    }
}
