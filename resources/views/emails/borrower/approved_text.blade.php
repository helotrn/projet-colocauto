@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $user->name }},

Félicitations, votre dossier de conduite est approuvé!

Vous pouvez maintenant emprunter les autos de vos voisins et voisines ;-)

Voir mon voisinage [{{ env('FRONTEND_URL') . '/community/list' }}]

L'équipe LocoMotion
info@locomotion.app
@endsection
