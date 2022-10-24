@extends('emails.layouts.main') @section('content')

<p
    style="
        text-align: center;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
        margin-bottom: 32px;
    "
>
    Bonjour {{ $user->name }},
</p>

<p
    style="
        text-align: center;
        margin: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    Félicitations, votre dossier de conduite est approuvé! Vous devez par contre
    attendre l'approbation de votre preuve de résidence pour pouvoir emprunter
    des véhicules.
</p>

@endsection
