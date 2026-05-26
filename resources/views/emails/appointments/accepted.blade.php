@extends('layouts.mail')

@section('content')
<h2 style="font-size: 20px; font-weight: 700; margin-bottom: 16px;">Rendez-vous confirmé</h2>
<p>Bonjour <strong>{{ $appointment->student->name }}</strong>,</p>
<p>Bonne nouvelle ! Votre demande de rendez-vous a été acceptée par votre conseiller. Nous avons hâte de vous accompagner.</p>

<div class="details">
    <div class="details-item"><strong>Conseiller :</strong> {{ $appointment->counselor->name }}</div>
    <div class="details-item"><strong>Date :</strong> {{ $appointment->appointment_date->format('d/m/Y') }}</div>
    <div class="details-item"><strong>Heure :</strong> {{ $appointment->appointment_time }}</div>
    <div class="details-item"><strong>Statut :</strong> <span class="badge badge-primary">CONFIRMÉ</span></div>
</div>

<p style="margin-top: 24px;">Vous pouvez désormais accéder à votre espace pour échanger avec votre conseiller via la messagerie sécurisée.</p>

<div style="text-align: center;">
    <a href="{{ config('app.frontend_url') }}/student/appointments" class="button">Voir mes rendez-vous</a>
</div>
@endsection
