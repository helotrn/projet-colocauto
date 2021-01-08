@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $receiver->name }},

{{ $admin->name }} a ajusté les données entrées lors de la prise de possession du
véhicule sur l'emprunt de votre {{ $loan->loanable->name }} qui commençait
à {{ $loan->departure_at }}.

Voir l'emprunt [{{ url('/loans/' . $loan->id) }}]

        - L'équipe LocoMotion
@endsection
