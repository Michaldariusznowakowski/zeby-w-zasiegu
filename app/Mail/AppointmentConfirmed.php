<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmed extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $doctor;
    public $time;
    public $date;
    public $description;
    public function __construct($user, $doctor, $time, $date, $description)
    {
        $this->user = $user;
        $this->doctor = $doctor;
        $this->time = $time;
        $this->date = $date;
        $this->description = $description;
    }
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Zęby w Zasięgu] Potwierdzenie wizyty',
        );
    }
    public function content(): Content
    {
        return new Content(
            view: 'mail.appointmentConfirmed',
        );
    }
    public function attachments(): array
    {
        return [];
    }
}
