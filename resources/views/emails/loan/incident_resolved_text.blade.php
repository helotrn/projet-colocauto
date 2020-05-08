@extends('emails.layouts.main')

@section('content')
    Bonjour {{ $target->user->name }},

    L'incident rapporté lors de l'emprunt de votre {{ $loan->loanable->name }} qui commençait à {{ $loan->departure_at }} a été résolu.

Voir l'emprunt [https://locomotion.app/loans/{{ $loan->id }}]

            - L'équipe LocoMotion
@endsection
