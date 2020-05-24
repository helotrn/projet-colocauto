@extends('emails.layouts.main')

@section('content')
<p>
    Vous recevez ce courriel parce que nous avons reçu une demande de réinitialisation de mot
    de passe.
</p>

<p>
    Ce lien expirera dans {{ $expiration }} heures.
</p>

<p style="text-align: center;">
<a href="{{ $route }}" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Réinitialiser le mot de passe</a>
</p>

<p>
    Si vous n'avez pas demandé de réinitialisation de mot de passe. Vous pouvez
    ignorer ce message.
</p>
@endsection
