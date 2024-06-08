<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewMessage extends Mailable
{
    use Queueable, SerializesModels;
    public $user;

    public $unreadedMessagesCount;
    public function __construct($user, $unreadedMessagesCount)
    {
        $this->user = $user;
        $this->unreadedMessagesCount = $unreadedMessagesCount;
    }
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Zęby w Zasięgu] Masz nową wiadomość',
        );
    }
    public function content(): Content
    {
        return new Content(
            view: 'mail.newMessage',
        );
    }
    public function attachments(): array
    {
        return [];
    }
}
