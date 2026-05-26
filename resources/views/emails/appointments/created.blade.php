@extends('layouts.mail')

@section('content')
<h2 style="font-size: 20px; font-weight: 700; margin-bottom: 16px;">Nouvelle demande de rendez-vous</h2>
<p>Bonjour <strong>{{ $appointment->counselor->name }}</strong>,</p>
<p>Un étudiant vient de solliciter un moment d'écoute. Voici les détails de la demande :</p>

<div class="details">
    <div class="details-item"><strong>Étudiant :</strong> {{ $appointment->student->name }}</div>
    <div class="details-item"><strong>Date :</strong> {{ $appointment->appointment_date->format('d/m/Y') }}</div>
    <div class="details-item"><strong>Heure :</strong> {{ $appointment->appointment_time }}</div>
    <div class="details-item"><strong>Type :</strong> <span class="badge badge-primary">{{ $appointment->type }}</span></div>
    <div class="details-item"><strong>Statut :</strong> <span class="badge badge-warning">EN ATTENTE</span></div>
</div>

<p style="margin-top: 24px;"><strong>Motif de la demande :</strong></p>
<p style="background: #f8fafc; padding: 16px; border-radius: 12px; font-style: italic; border-left: 4px solid #0f766e;">
    &quot;{{ $appointment->reason }}&quot;
</p>

<div style="text-align: center;">
    <a href="{{ config('app.frontend_url') }}/counselor/appointments/{{ $appointment->id }}" class="button">Consulter la demande</a>
</div>
@endsection
