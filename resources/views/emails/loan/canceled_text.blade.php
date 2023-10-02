@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $receiver->name }},

{{ $sender->name }} a annulé l'emprunt de {{ $loan->loanable->name }} du {{ $loan->departure_at }}

Voir l'emprunt [{{ env('FRONTEND_URL') . '/loans/' . $loan->id }}]

        - L'équipe Coloc'Auto
@endsection
