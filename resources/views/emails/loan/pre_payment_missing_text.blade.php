@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $user->name }},

Votre réservation d'auto commence dans 24 heures et n'a pas encore été prépayée. Merci de
procéder au prépaiement avant d'aller chercher le véhicule chez votre voisin. Pour ce faire,
rendez-vous sur la page de l'emprunt.

Voir l'emprunt [{{ env('FRONTEND_URL') . '/loans/' . $loan->id }}]

Merci de votre participation !

L'équipe LocoMotion
info@locomotion.app
@endsection
