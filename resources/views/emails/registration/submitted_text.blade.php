@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $user->name }},

Votre profil est complété. Bienvenue dans LocoMotion, c'est un départ ! 

Pour pouvoir utiliser les véhicules il vous faut maintenant:

1/ vous procurer votre trousse de départ

un attache-remorque pour les vélos
carnet de bord pour les autos
un autocollant à afficher fièrement ! 
2/ Installer Noke pro pour ouvrir les cadenas de nos 2 roues et les boîtes à clés.

⚠️ Vous allez recevoir un message de Noke Pro(en anglais), il ne s'agit pas d'une tentative de hameçonnage ! Le courriel (peut être dans le dossier « spam ») ou le texto servent à activer votre compte Noke Pro pour débarrer les cadenas. 

Voir la marche à suivre : [{{ url('http://bit.ly/locomotion-bienvenue') }}]

Pour utiliser l'auto d'une personne du voisinage, ajoutez vos documents à Mon dossier de conduite. Commandez-les dès maintenant :

Pour commander votre dossier de conduite SAAQ : [{{ url('https://saaq.gouv.qc.ca/services-en-ligne/citoyens/demander-copie-dossier-conduite/') }}]
Pour commander votre rapport de sinistre GAA : [{{ url('https://gaa.qc.ca/fr/fichier-central-des-sinistres-automobiles/votre-dossier-de-sinistres/') }}]
Vous aimeriez mettre en commun votre véhicule personnel (auto, vélo, remorques à vélo...) ? Ajoutez-les à votre profil dans : Mes véhicules

Vous voulez jaser et posez des questions ? Envoyez-nous un courriel, il nous fera un plaisir de vous rencontrer !

À bientôt,

L'équipe LocoMotion
info@locomotion.app

@endsection
