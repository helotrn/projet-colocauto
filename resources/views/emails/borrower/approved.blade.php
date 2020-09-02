@extends('emails.layouts.main')

@section('content')
<p>
    Bonjour {{ $user->name }},
</p>

<p>
    Félicitations, votre dossier de conduite est approuvé!
</p>

<p>
    Vous pouvez maintenant emprunter les autos de vos voisins et voisines ;-)
</p>

<p style="text-align: center;">
<a href="{{ url('/community/list') }}" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir mon voisinage</a>
</p>

<p>L'équipe LocoMotion<br>
info@locomotion.app</p>
@endsection
