@extends('emails.layouts.main')

@section('content')
<p>
    Bonjour {{ $user->name }},
</p>

<p>
    Vous avez bien complété votre dossier de conduite. Un.e membre de l'équipe va l'examiner.
    Vous serez informé lorsque votre dossier de conduite sera approuvé.
</p>

<p>L'équipe LocoMotion<br>
info@locomotion.app</p>
@endsection
