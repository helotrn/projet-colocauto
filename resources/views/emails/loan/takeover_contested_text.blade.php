@extends('emails.layouts.main')

@section('content')
Bonjour {{ $receiver->name }},

{{ $caller->name }} a contesté les données entrées lors de la prise de possession du
véhicule sur son emprunt de votre {{ $loan->loanable->name }} qui commençait
à {{ $loan->departure_at }}.

@if (!!$takeover->comments_on_contestation)
"
{{ $takeover->comments_on_contestation }}
"
@endif

Un membre de l'équipe Coloc'Auto a été notifié et sera appelé à arbitrer la résolution du
problème.

Voir l'emprunt [{{ env('FRONTEND_URL') . '/loans/' . $loan->id }}]

        - L'équipe Coloc'Auto
@endsection
