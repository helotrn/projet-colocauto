@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $user->name }},

Merci d'utiliser le programme Coloc'Auto!

Votre réservation commence dans 3 heures, veuillez prendre connaisance de la marche
à suivre si ce n'est pas déjà fait.

Si vous ne prevoyez pas utiliser le véhicule vous pouvez annuler la réservation
ici [{{ env('FRONTEND_URL') . '/loans/' . $loan->id }}].

Merci de votre participation!

            - L'équipe Coloc'Auto
@endsection
