@extends('emails.layouts.main')

@section('content')
Bonjour {{ $borrower->user->name }},

{{ $owner->user->name }} a accepté votre demande d'emprunt de {{ $loan->loanable->name }}
à partir de {{ $loan->departure_at }}.

{{ $intention->message_for_borrower }}

Voir l'emprunt [{{ url('/loans/' . $loan->id) }}]

            - L'équipe LocoMotion
@endsection
