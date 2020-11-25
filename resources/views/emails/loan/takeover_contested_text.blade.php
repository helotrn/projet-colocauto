@extends('emails.layouts.main')

@section('content')
Bonjour {{ $receiver->name }},

{{ $caller->name }} a contesté les données entrées lors de la prise de possession du
véhicule sur son emprunt de votre {{ $loan->loanable->name }} qui commençait
à {{ $loan->departure_at }}.

"
{{ $takeover->comments_on_contestation }}
"

Un membre de l'équipe LocoMotion a été notifié et sera appelé à arbitrer la résolution du
problème.

Voir l'emprunt [{{ url('/loans/' . $loan->id) }}]

        - L'équipe LocoMotion
@endsection
