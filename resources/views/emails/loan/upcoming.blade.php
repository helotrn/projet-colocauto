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
    Merci d'utiliser le programme LocoMotion!
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
    Votre réservation commence dans 3 heures environ, veuillez prendre
    connaisance de la marche à suivre si ce n'est pas déjà fait.
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
    Si vous ne prévoyez pas utiliser le véhicule, vous pouvez annuler la
    réservation
    <a href="{{ env('FRONTEND_URL') . '/loans/' . $loan->id }}">ici</a>.
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
    Merci de votre participation!
</p>

@endsection
