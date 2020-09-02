@extends('emails.layouts.main')

@section('content')
<p>
    Ça y est! Vous pouvez maintenant commencer à prêter ou emprunter des véhicules près de
    chez vous en vous connectant à locomotion.app .
</p>

<p>
    Pour bien participer à ce projet de partage,
</p>

<p style="text-align: center;">
<a href="http://bit.ly/locomotion-bienvenue" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">→ Voir la marche à suivre</a>
</p>

<p>
    Vous pouvez dès maintenant emprunter les vélos et remorques à vélo. Pour emprunter une
    auto, vous devez compléter votre
    «&nbsp;<a href="{{ url('/profile/borrower') }}" target="_blank">Mon dossier de conduite</a>&nbsp;».
</p>

<p>
    Pour partager votre véhicule personnel avec vos voisin-e-s  (auto, vélo-cargo, vélo
    électrique...), ajoutez-le à votre profil dans
    «&nbsp;<a href="{{ url('/profile/loanables') }}" target="_blank">Mes véhicules</a>&nbsp;».
    Bien sûr, vous avez toujours la main pour décider ou non de prêter votre véhicule lorsque
    vous recevez une demande. Assurances, compensation, … Toutes les réponses sont dans la
    <a href="{{ url('/faq') }}" target="_blank">FAQ</a>!
</p>

<p>
    LocoMotion est un projet porté par les citoyen-ne-s, avec le soutien de Solon! Voyez qui
    sont vos voisin-e-s et comment vous impliquer dans le projet via
    «&nbsp;<a href="{{ url('/community') }}" target="_blank">Mon voisinage</a>&nbsp;».
</p>

<p>
    Vous avez encore des questions? Envoyez-nous un courriel, il nous fera un plaisir de vous répondre.
</p>

<p>
    À bientôt,
</p>

<p>L'équipe LocoMotion<br>
info@locomotion.app</p>
@endsection
