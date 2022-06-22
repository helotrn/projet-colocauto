@extends('emails.layouts.main_text')

@section('content')
Bonjour,

Vous recevez ce courriel parce que nous avons reçu une demande de réinitialisation de mot de passe pour votre adresse courriel.

Rendez-vous à cette adresse pour effectuer la réinitialisation :

{{ $route }}

Ce lien expirera dans {{ $expiration }} heures.

Si vous n'avez pas demandé de réinitialisation de mot de passe. Vous pouvez ignorer ce message.

L'équipe LocoMotion
info@locomotion.app
@endsection
