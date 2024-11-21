@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $borrower->user->name }},

Votre réservation du véhicule {{ $loan->loanable->name }} a été modifiée par
{{ $user->name }}. Elle conmmence maintenant à partir du
@datetime($loan->departure_at) et pour une durée de
@duration($loan->duration_in_minutes).

{{ $loan->message_for_owner }}

Voir l'emprunt [{{ env('FRONTEND_URL') . '/loans/' . $loan->id }}]

            - L'équipe Coloc'Auto
@endsection
