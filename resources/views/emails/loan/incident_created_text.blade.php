@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $owner->user->name }},

{{ $borrower->user->name }} a rapporté un incident lors de son emprunt de votre {{ $loan->loanable->name }} qui commençait à {{ $loan->departure_at }}.

{{ $incident->comments_on_incident }}

Voir l'emprunt [{{ env('FRONTEND_URL') . '/loans/' . $loan->id }}]

            - L'équipe LocoMotion
@endsection
