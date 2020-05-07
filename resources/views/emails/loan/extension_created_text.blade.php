@extends('emails.layouts.main')

@section('content')
Bonjour {{ $owner->user->name }},

{{ $borrower->user->name }} a demandé à rallong son emprunt de votre {{ $loan->loanable->name }} qui commençait à {{ $loan->departure_at }} et qui durerait maintenant {{ $extension->new_duration }} minutes.

{{ $loan->comments_on_extension }}

Voir l'emprunt [https://locomotion.app/loans/{{ $loan->id }}]

            - L'équipe LocoMotion
@endsection
