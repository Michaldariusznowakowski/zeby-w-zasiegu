<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentDoctorInfo extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $doctor;
    public $patient;
    public $time;
    public $date;
    public $description;
    public function __construct($user, $doctor, $time, $date, $description, $patient)
    {
        $this->user = $user;
        $this->doctor = $doctor;
        $this->time = $time;
        $this->date = $date;
        $this->description = $description;
        $this->patient = $patient;
    }
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Zęby w Zasięgu] Nowa rezerwacja wizyty',
        );
    }
    public function content(): Content
    {
        return new Content(
            view: 'mail.appointmentDoctorInfo',
        );
    }
    public function attachments(): array
    {
        return [];
    }
}
