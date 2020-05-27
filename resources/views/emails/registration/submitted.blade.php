@extends('emails.layouts.main')

@section('content')
<p>
    Bonjour {{ $user->name }},
</p>

<p>Votre profil est complété. Bienvenue dans LocoMotion, c'est un départ ! <br /></p>
<p>Pour pouvoir utiliser les véhicules il vous faut maintenant: </p>
<p>1/ vous procurer votre trousse de départ</p>
<ul>
<li>un attache-remorque pour les vélos</li>
<li>carnet de bord pour les autos</li>
<li>un autocollant à afficher fièrement ! <br /></li>
</ul>
<p>2/ Installer Noke pro pour ouvrir les cadenas de nos 2 roues et les boîtes à clés.</p>
<p style="background:#F9CA51">⚠️ Vous allez recevoir un message de Noke Pro(en anglais), il ne s'agit pas d'une tentative de hameçonnage ! Le courriel (peut être dans le dossier « spam ») ou le texto servent à activer votre compte Noke Pro pour débarrer les cadenas. <br /></p>
	<p style="text-align: center;">
	<a to="{{ url('http://bit.ly/locomotion-bienvenue') }}" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">
	Voir la marche à suivre</a>
	</p>
</p>
<p>Pour utiliser l'auto d'une personne du voisinage, ajoutez vos documents à <a href="{{ url('/profile/borrower') }}"  target="_blank">Mon dossier de conduite</a>. Commandez-les dès maintenant :</p>
<ul>
<li>
 <a to="{{ url('https://saaq.gouv.qc.ca/services-en-ligne/citoyens/demander-copie-dossier-conduite/') }}" target="_blank">Pour commander votre dossier de conduite SAAQ</a>
</li>
<li>
 <a to="{{ url('https://gaa.qc.ca/fr/fichier-central-des-sinistres-automobiles/votre-dossier-de-sinistres/') }}" target="_blank">Pour commander votre rapport de sinistre GAA</a>
</li>
</ul>
<p>Vous aimeriez mettre en commun votre véhicule personnel (auto, vélo, remorques à vélo...) ? Ajoutez-les à votre profil dans : 
 <a href="{{ url('/profile/loanables') }}"  target="_blank">Mes véhicules</a>
</p>
<p>Vous voulez jaser et posez des questions ? Envoyez-nous un courriel, il nous fera un plaisir de vous rencontrer !</p>
<p>À bientôt,</p>
<p>L'équipe LocoMotion</p>
<p>info@locomotion.app</p>
@endsection
