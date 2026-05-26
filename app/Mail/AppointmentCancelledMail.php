<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public string $cancelledByRole
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Annulation de rendez-vous',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointments.cancelled',
            with: [
                'appointment' => $this->appointment,
                'cancelledByRole' => $this->cancelledByRole,
            ],
        );
    }
}
