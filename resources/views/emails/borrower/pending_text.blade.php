@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $user->name }},

Félicitations, votre dossier de conduite est approuvé!

Vous devez par contre attendre l'approbation de votre preuve de résidence pour pouvoir emprunter des véhicules. 

L'équipe LocoMotion
info@locomotion.app
@endsection
