<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentCancelled extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $time;
    public $date;
    public $did_doctor_cancel;
    public function __construct($user, $time, $date, $did_doctor_cancel)
    {
        $this->user = $user;
        $this->time = $time;
        $this->date = $date;
        $this->did_doctor_cancel = $did_doctor_cancel;
    }
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Zęby w Zasięgu] Wizyta została odwołana',
        );
    }
    public function content(): Content
    {
        if ($this->did_doctor_cancel) {
            return new Content(
                view: 'mail.appointmentCancelledDoctor'
            );
        } else {
            return new Content(
                view: 'mail.appointmentCancelledPatient'
            );
        }
    }
    public function attachments(): array
    {
        return [];
    }
}
