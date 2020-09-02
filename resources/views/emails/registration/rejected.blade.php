@extends('emails.layouts.main')

@section('content')
<p>
    Bonjour {{ $user->name }},
</p>

<p>
    Votre demande d'adhésion à LocoMotion a été refusée pour une des raisons suivantes:
</p>

<ol>
    <li>Votre adresse n'est pas localisée dans un des voisinages.</li>
    <li>Il y a un problème avec votre preuve de résidence.</li>
</ol>

<p>
    Pour plus de détails, communiquez avec l'équipe LocoMotion au courriel
    <a href="mailto:info@locomotion.app">info@locomotion.app</a> .
</p>

<p>L'équipe LocoMotion<br>
info@locomotion.app</p>
@endsection
