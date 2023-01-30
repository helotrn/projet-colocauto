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
    Bonjour {{ $user->name }},
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
    Votre demande d'adhésion à Coloc'Auto a été refusée pour une des raisons
    suivantes:
</p>

<ol
    style="
        text-align: left;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    <li>Votre adresse n'est pas localisée dans un des voisinages.</li>
    <li>Il y a un problème avec votre preuve de résidence.</li>
</ol>

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
    Pour plus de détails, communiquez avec l'équipe Coloc'Auto au courriel
    <a href="mailto:soutien@colocauto.org">soutien@colocauto.org</a> .
</p>

@endsection
