@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $receiver->name }},

{{ $admin->name }} a ajusté les données entrées lors du retour du
véhicule sur l'emprunt de votre {{ $loan->loanable->name }} qui commençait
à {{ $loan->departure_at }}.

Voir l'emprunt [{{ env('FRONTEND_URL') . '/loans/' . $loan->id }}]

        - L'équipe Coloc'Auto
@endsection
