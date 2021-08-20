@extends('emails.layouts.main_text')

@section('content')
Ça y est! Vous pouvez maintenant commencer à prêter ou emprunter des véhicules près de
chez vous en vous connectant à locomotion.app .

Pour bien participer à ce projet de partage,

→ Voir la marche à suivre [http://bit.ly/locomotion-bienvenue]

Vous pouvez dès maintenant emprunter les vélos et remorques à vélo. Pour emprunter une
auto, vous devez compléter votre « Mon dossier de conduite [{{ env('FRONTEND_URL') . '/profile/borrower' }}] ».

Pour partager votre véhicule personnel avec vos voisin-e-s  (auto, vélo-cargo, vélo
électrique...), ajoutez-le à votre profil dans « Mes véhicules [{{ env('FRONTEND_URL') . '/profile/loanables' }}] ». Bien sûr, vous avez toujours la main pour décider ou non de prêter votre véhicule lorsque
vous recevez une demande. Assurances, compensation, … Toutes les réponses sont dans la
FAQ [{{ env('FRONTEND_URL') . '/faq' }}]!

LocoMotion est un projet porté par les citoyen-ne-s, avec le soutien de Solon! Voyez qui
sont vos voisin-e-s et comment vous impliquer dans le projet via
« Mon voisinage [{{ env('FRONTEND_URL') . '/community' }}] ».

Vous avez encore des questions? Envoyez-nous un courriel, il nous fera un plaisir de vous répondre.

À bientôt,

L'équipe LocoMotion
info@locomotion.app
@endsection
