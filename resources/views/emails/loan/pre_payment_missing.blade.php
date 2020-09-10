@extends('emails.layouts.main')

@section('content')
<p>
    Bonjour {{ $user->name }},
</p>

<p>
  Votre réservation d'auto commence dans 24 heures et n'a pas encore été prépayée. Merci de
  procéder au prépaiement avant d'aller chercher le véhicule chez votre voisin. Pour ce faire,
  rendez-vous sur la page de l'emprunt.
</p>

<p style="text-align: center;">
<a href="{{ url('/loans/' . $loan->id) }}" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir l'emprunt</a>
</p>

<p>
  Merci de votre participation !
</p>

<p>
L'équipe LocoMotion<br>
info@locomotion.app
</p>
@endsection
