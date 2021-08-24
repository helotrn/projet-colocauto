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
    Ça y est! Vous pouvez maintenant commencer à prêter ou emprunter des
    véhicules près de chez vous en vous connectant à locomotion.app .
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
    Pour bien participer à ce projet de partage,
</p>

<p style="text-align: center; margin: 32px">
    <a
        href="http://bit.ly/locomotion-bienvenue"
        style="
            display: inline-block;
            background-color: #246aea;
            padding: 8px 16px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            font-size: 17px;
            line-height: 24px;
            text-decoration: none;
        "
        target="_blank"
        >→ Voir la marche à suivre</a
    >
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
    Vous pouvez dès maintenant emprunter les vélos et remorques à vélo. Pour
    emprunter une auto, vous devez compléter votre «&nbsp;<a
        href="{{ env('FRONTEND_URL') . '/profile/borrower' }}"
        target="_blank"
        >Mon dossier de conduite</a
    >&nbsp;».
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
    Pour partager votre véhicule personnel avec vos voisin-e-s (auto,
    vélo-cargo, vélo électrique...), ajoutez-le à votre profil dans «&nbsp;<a
        href="{{ env('FRONTEND_URL') . '/profile/loanables' }}"
        target="_blank"
        >Mes véhicules</a
    >&nbsp;». Bien sûr, vous avez toujours la main pour décider ou non de prêter
    votre véhicule lorsque vous recevez une demande. Assurances, compensation, …
    Toutes les réponses sont dans la
    <a href="{{ env('FRONTEND_URL') . '/faq' }}" target="_blank">FAQ</a>!
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
    LocoMotion est un projet porté par les citoyen-ne-s, avec le soutien de
    Solon! Voyez qui sont vos voisin-e-s et comment vous impliquer dans le
    projet via «&nbsp;<a
        href="{{ env('FRONTEND_URL') . '/community' }}"
        target="_blank"
        >Mon voisinage</a
    >&nbsp;».
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
    Vous avez encore des questions? Envoyez-nous un courriel, il nous fera un
    plaisir de vous répondre.
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
    À bientôt,
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
    L'équipe LocoMotion
</p>
@endsection
