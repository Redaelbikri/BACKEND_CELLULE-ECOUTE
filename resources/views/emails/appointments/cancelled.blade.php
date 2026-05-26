@extends('layouts.mail')

@section('content')
<h2 style="font-size: 20px; font-weight: 700; margin-bottom: 16px;">Annulation de rendez-vous</h2>
<p>Bonjour <strong>{{ $cancelledByRole === 'student' ? $appointment->counselor->name : $appointment->student->name }}</strong>,</p>
<p>Nous vous informons qu'un rendez-vous a été annulé. Voici les détails concernés :</p>

<div class="details">
    <div class="details-item"><strong>Date :</strong> {{ $appointment->appointment_date->format('d/m/Y') }}</div>
    <div class="details-item"><strong>Heure :</strong> {{ $appointment->appointment_time }}</div>
    <div class="details-item"><strong>Statut :</strong> <span style="background-color: #f1f5f9; color: #475569;" class="badge">ANNULÉ</span></div>
</div>

<p style="margin-top: 24px; color: #64748b; font-style: italic;">
    {{ $cancelledByRole === 'student'
        ? "L'étudiant a annulé ce rendez-vous. Ce créneau est désormais libre dans votre agenda."
        : "Le rendez-vous a été annulé. N'hésitez pas à solliciter un nouveau créneau si vous souhaitez poursuivre l'accompagnement." }}
</p>

<div style="text-align: center;">
    <a href="{{ config('app.frontend_url') }}/{{ $cancelledByRole === 'student' ? 'counselor' : 'student' }}/appointments" class="button">Voir mon agenda</a>
</div>
@endsection
