@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $user->name }},

Votre demande d'adhésion à LocoMotion a été refusée pour une des raisons suivantes:

    1. Votre adresse n'est pas localisée dans un des voisinages.
    2. Il y a un problème avec votre preuve de résidence.

Pour plus de détails, communiquez avec l'équipe LocoMotion au courriel info@locomotion.app .

L'équipe LocoMotion
info@locomotion.app
@endsection
