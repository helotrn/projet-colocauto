@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $user->name }},

Félicitations, votre véhicule {{ $loanable->name }} a bien été ajouté!

Pour les autos en partage, allez chercher un carnet de bord chez les commerces
participants et déposez le dans votre coffre à gants.

Vous recevrez un message quand une personne de la communauté voudra l'utiliser.

N'oubliez pas, en tout temps, vous pouvez modifier sa disponibilité.

            - L'équipe Coloc'Auto
@endsection
