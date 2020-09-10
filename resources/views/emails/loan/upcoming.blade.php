@extends('emails.layouts.main')

@section('content')
<p>
    Bonjour {{ $user->name }},
</p>

<p>
    Merci d'utiliser le programme LocoMotion!
</p>

<p>
    Votre réservation commence dans 3 heures environ, veuillez prendre connaisance de la marche
    à suivre si ce n'est pas déjà fait.
</p>

<p>
    Si vous ne prévoyez pas utiliser le véhicule, vous pouvez annuler la réservation
    <a href="{{ url('/loans/' . $loan->id) }}">ici</a>.
</p>

<p>
    Merci de votre participation!
</p>

<p style="text-align: right;">
    <em>- L'équipe LocoMotion</em>
</p>
@endsection
