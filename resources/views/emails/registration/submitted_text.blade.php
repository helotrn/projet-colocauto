@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $user->name }},

Votre profil est complété, bienvenue dans LocoMotion !

Vous pouvez maintenant partager auto, vélo ... et pédalo ? Ça, ça dépend de vos voisines et voisins. ;-)

Voir mon voisinage [https://locomotion.app/community]

            - L'équipe LocoMotion
@endsection
