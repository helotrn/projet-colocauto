@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $user->name }},

Vous avez bien complété votre dossier de conduite. Un-e membre de l'équipe va l'examiner.
Vous serez informé lorsque votre dossier de conduite sera approuvé.

L'équipe LocoMotion
info@locomotion.app
@endsection
