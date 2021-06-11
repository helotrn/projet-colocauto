@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $user->name }},

Félicitations, votre véhicule {{ $loanable->name }} a bien été ajouté!

Pour les autos en partage, allez chercher un carnet de bord chez les commerces
participants et déposez le dans votre coffre à gants.

La liste des commerces participants peut être obtenue à cette adresse :
https://mailchi.mp/solon-collectif/locomotion-comment-ca-marche#trousse%20de%20d%C3%A9part

Vous recevrez un message quand une personne du voisinage voudra l'utiliser.

N'oubliez pas, en tout temps, vous pouvez modifier sa disponibilité.

            - L'équipe LocoMotion
@endsection
