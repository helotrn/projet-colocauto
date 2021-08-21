@extends('emails.layouts.main') @section('content')
<p
    style="
        text-align: justify;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    Bonjour {{ $user->name }},
</p>

<p
    style="
        text-align: justify;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    Votre réservation d'auto commence dans 24 heures et n'a pas encore été
    prépayée. Merci de procéder au prépaiement avant d'aller chercher le
    véhicule chez votre voisin. Pour ce faire, rendez-vous sur la page de
    l'emprunt.
</p>

<p style="text-align: center; margin: 32px">
    <a
        href="{{ env('FRONTEND_URL') . '/loans/' . $loan->id }}"
        style="
            display: inline-block;
            background-color: #246aea;
            padding: 8px 16px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            font-size: 17px;
            line-height: 24px;
            text-decoration: none;
        "
        target="_blank"
        >Voir l'emprunt</a
    >
</p>

<p
    style="
        text-align: justify;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    Merci de votre participation !
</p>

@endsection
