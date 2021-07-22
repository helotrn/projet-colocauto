@extends('emails.layouts.main')

@section('content')
<p style="text-align: justify; margin-top: 0; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
    Bonjour {{ $user->name }},
</p>

<p style="text-align: justify; margin-top: 0; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
    Votre demande d'adhésion à LocoMotion a été refusée pour une des raisons suivantes:
</p>

<ol style="text-align: justify; margin-top: 0; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
    <li>Votre adresse n'est pas localisée dans un des voisinages.</li>
    <li>Il y a un problème avec votre preuve de résidence.</li>
</ol>

<p style="text-align: justify; margin-top: 0; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
    Pour plus de détails, communiquez avec l'équipe LocoMotion au courriel
    <a href="mailto:info@locomotion.app">info@locomotion.app</a> .
</p>

@endsection
