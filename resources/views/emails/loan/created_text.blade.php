@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $owner->user->name }},

{{ $borrower->user->name }} a réservé votre véhicule {{ $loan->loanable->name }}
à partir de @datetime($loan->departure_at) et pour un total de @duration($loan->duration_in_minutes).

{{ $loan->message_for_owner }}

Voir l'emprunt [{{ env('FRONTEND_URL') . '/loans/' . $loan->id }}]

            - L'équipe Coloc'Auto
@endsection
