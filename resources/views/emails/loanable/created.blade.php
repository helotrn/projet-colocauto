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
    Félicitations, votre véhicule {{ $loanable->name }} a bien été ajouté!
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
    Pour les autos en partage, allez chercher un carnet de bord chez les
    <a
        href="https://mailchi.mp/solon-collectif/locomotion-comment-ca-marche#trousse%20de%20d%C3%A9part"
        >commerces participants</a
    >
    et déposez le dans votre coffre à gants.
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
    Vous recevrez un message quand une personne du voisinage voudra l'utiliser.
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
    N'oubliez pas, en tout temps, vous pouvez modifier sa disponibilité.
</p>

@endsection
