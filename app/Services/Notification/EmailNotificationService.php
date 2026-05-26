<?php

namespace App\Services\Notification;

use App\Mail\AppointmentAcceptedMail;
use App\Mail\AppointmentCancelledMail;
use App\Mail\AppointmentCreatedMail;
use App\Mail\AppointmentRejectedMail;
use App\Models\Appointment;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailNotificationService
{
    public function sendAppointmentCreated(Appointment $appointment): void
    {
        if (! $appointment->counselor?->email) {
            return;
        }

        $this->sendSafely(
            $appointment->counselor->email,
            new AppointmentCreatedMail($appointment),
            'appointment_created'
        );
    }

    public function sendAppointmentAccepted(Appointment $appointment): void
    {
        if (! $appointment->student?->email) {
            return;
        }

        $this->sendSafely(
            $appointment->student->email,
            new AppointmentAcceptedMail($appointment),
            'appointment_accepted'
        );
    }

    public function sendAppointmentRejected(Appointment $appointment): void
    {
        if (! $appointment->student?->email) {
            return;
        }

        $this->sendSafely(
            $appointment->student->email,
            new AppointmentRejectedMail($appointment),
            'appointment_rejected'
        );
    }

    public function sendAppointmentCancelled(Appointment $appointment, string $cancelledByRole): void
    {
        $recipient = $cancelledByRole === 'student'
            ? $appointment->counselor?->email
            : $appointment->student?->email;

        if (! $recipient) {
            return;
        }

        $this->sendSafely(
            $recipient,
            new AppointmentCancelledMail($appointment, $cancelledByRole),
            'appointment_cancelled'
        );
    }

    private function sendSafely(string $recipient, Mailable $mailable, string $context): void
    {
        try {
            Mail::to($recipient)->send($mailable);
        } catch (\Throwable $exception) {
            Log::warning('Appointment email notification failed.', [
                'context' => $context,
                'recipient' => $recipient,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
