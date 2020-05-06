@extends('emails.layouts.main')

@section('content')
Bonjour {{ $user->name }},

Félicitations, votre véhicule {{ $loanable->name }} a bien été ajouté!

Vous recevrez un message quand une personne du voisinage voudra l'utiliser.

N'oubliez pas, en tout temps, vous pouvez modifier sa disponibilité.

            - L'équipe LocoMotion
@endsection
