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
    Vous recevez ce courriel parce que nous avons reçu une demande de
    réinitialisation de mot de passe.
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
    Ce lien expirera dans {{ $expiration }} heures.
</p>

<p style="text-align: center; margin: 32px">
    <a
        href="{{ $route }}"
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
        >Réinitialiser le mot de passe</a
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
    Si vous n'avez pas demandé de réinitialisation de mot de passe. Vous pouvez
    ignorer ce message.
</p>
@endsection
