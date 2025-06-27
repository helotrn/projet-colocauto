@extends('emails.layouts.main') @section('content')
<p
    style="
        text-align: center;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
        margin-bottom: 32px;
    "
>
    Vous recevez ce courriel parce que nous avons reçu une demande de
    réinitialisation de mot de passe.
</p>

<p
    style="
        text-align: center;
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
    @include('emails.partials.button', [
        'url' => $route,
        'text' => 'Réinitialiser le mot de passe'
    ])
</p>

<p
    style="
        text-align: center;
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
