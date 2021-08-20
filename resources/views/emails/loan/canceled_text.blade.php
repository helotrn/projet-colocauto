@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $receiver->name }},

{{ $sender->name }} a annulé l'emprunt à votre {{ $loan->loanable->name }}
à partir de {{ $loan->departure_at }} et pour une durée de {{ $loan->duration_in_minutes }} minutes.

Voir l'emprunt [{{ env('FRONTEND_URL') . '/loans/' . $loan->id }}]

        - L'équipe LocoMotion
@endsection
