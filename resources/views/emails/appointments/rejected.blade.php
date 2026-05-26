@extends('layouts.mail')

@section('content')
<h2 style="font-size: 20px; font-weight: 700; margin-bottom: 16px;">Demande de rendez-vous non retenue</h2>
<p>Bonjour <strong>{{ $appointment->student->name }}</strong>,</p>
<p>Nous vous informons que votre demande de rendez-vous pour le créneau sélectionné n'a pas pu être acceptée pour le moment.</p>

<div class="details">
    <div class="details-item"><strong>Conseiller :</strong> {{ $appointment->counselor->name }}</div>
    <div class="details-item"><strong>Date :</strong> {{ $appointment->appointment_date->format('d/m/Y') }}</div>
    <div class="details-item"><strong>Heure :</strong> {{ $appointment->appointment_time }}</div>
    <div class="details-item"><strong>Statut :</strong> <span style="background-color: #fee2e2; color: #991b1b;" class="badge">NON RETENU</span></div>
</div>

<p style="margin-top: 24px;">Ne vous découragez pas. Vous pouvez dès maintenant solliciter un nouveau créneau ou choisir un autre conseiller disponible sur la plateforme.</p>

<div style="text-align: center;">
    <a href="{{ config('app.frontend_url') }}/student/appointments/create" class="button">Choisir un autre créneau</a>
</div>
@endsection
