@extends('emails.layouts.main')

@section('content')
Bonjour {{ $owner->user->name }},

{{ $borrower->user->name }} a rapporté un incident lors de son emprunt de votre {{ $loan->loanable->name }} qui commençait à {{ $loan->departure_at }}.

{{ $incident->comments_on_incident }}

Voir l'emprunt [https://locomotion.app/loans/{{ $loan->id }}]

            - L'équipe LocoMotion
@endsection
