@extends('emails.layouts.main')

@section('content')
Bonjour {{ $owner->user->name }},

{{ $borrower->user->name }} a demandé à emprunter votre {{ $loan->loanable->name }}
à partir de {{ $loan->departure_at }} et pour un total de {{ $loan->duration_in_minutes }}.

{{ $loan->message_for_owner }}

Voir l'emprunt [{{ url('/loans/' . $loan->id) }}]

            - L'équipe LocoMotion
@endsection
