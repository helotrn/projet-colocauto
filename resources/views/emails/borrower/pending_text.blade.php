@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $user->name }},

Félicitations, votre dossier de conduite est approuvé! 

@if($isRegistrationSubmitted)
    Vous devez par contre attendre l'approbation de votre preuve de résidence pour pouvoir emprunter des véhicules. 
@else
    Vous devez par contre soumettre votre preuve de résidence pour pouvoir emprunter des véhicules. 
@endif

L'équipe LocoMotion
info@locomotion.app
@endsection
