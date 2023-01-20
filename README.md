# LocoMotion

Application et API de LocoMotion

[![pipeline status](https://gitlab.com/Solon-collectif/locomotion.app/badges/master/pipeline.svg)](https://gitlab.com/Solon-collectif/locomotion.app/-/commits/master)
[![coverage report](https://gitlab.com/Solon-collectif/locomotion.app/badges/master/coverage.svg)](https://gitlab.com/Solon-collectif/locomotion.app/-/commits/master)

## Contribuer

Consultez le fichier [CONTRIBUTING.md](CONTRIBUTING.md)

## Prérequis

-   docker 20
-   docker-compose 3.6

## Démarrage

-   `docker-compose up --build`

## Outils

Le dossier [devtools](devtools) contient des alias pour simplifier le développement dans l'application. Voyez le [README](devtools/README.md) pour les instructions d'utilisation. Le reste des instructions qui suivent font références à ces alias.

## Initialisation de la base de données et de MinIO

Une fois que l'application est démarrée,
dans un autre terminal, faire:

-   `docker-compose exec php php artisan migrate --seed`
-   ou l'alias `locoseed`

## Variables d'environnement

### Généralités

1. Aucune variable confidentielle ne doit être présente dans le code source.

2. Lors de vos merge requests, veuillez indiquer l'ajout, la modification ou la supression d'une variable d'environment en production ou en staging.

### Back-end

Les variables d'environnement sont dans `docker-compose.yaml`.

### Vue.js

`.env.example` Contient la liste des variables nécessaire au bon fonctionnement du front-end.
`.env.development` contient les variables et leurs valeurs.

Le nom de toutes variables doit commencer par `VUE_APP_` ([doc](https://cli.vuejs.org/guide/mode-and-env.html#using-env-variables-in-client-side-code)).

`env.production` et `.env.staging` sont générés dynamiquement à partir de [Google Cloud Run](https://console.cloud.google.com/run?project=locomotion-320712) dans la section 'VARIABLES & SECRETS' dans 'EDIT & DEPLOY NEW REVISION'. La personne en charge du déploiement se chargera des modifications.

## Se connecter à l'application

Les comptes suivants auront été créés:

-   soutien@locomotion.app (admin)
-   solonahuntsic@locomotion.app
-   solonpetitepatrie@locomotion.app
-   emprunteurahuntsic@locomotion.app
-   proprietaireahuntsic@locomotion.app
-   emprunteurpetitepatrie@locomotion.app
-   proprietairepetitepatrie@locomotion.app

Le mot de passe pour chacun des comptes est **locomotion**

Il est possible depuis le compte admin de se connecter comme n'importe quel autre membre.

## Tests

Tests au complet, exécuter:

`docker-compose exec php ./vendor/bin/phpunit` ou l'alias `locotest` qui va s'assurer que la base de données de test est à jour.

:warning: à exécuter avant de soumettre votre MR.

Pour tester une fraction des tests localement,

`docker-compose exec php bash ./vendor/bin/phpunit --filter ReviewableTest` ou l'alias `locotest --filter ReviewableTest`.

Les variables dans l'environnement de test se trouvent dans: `.env.testing` ou `phpunit.xml`.

Pour en savoir plus, [voici un article utile](https://mattstauffer.com/blog/environment-specific-variables-in-laravels-testing-environment).

## Utilisation de prettier

Nous utilisons [prettier](https://prettier.io/) et [prettier-php](https://github.com/prettier/plugin-php) pour le formattage du code.

Pour VS Code, vous devez installer **prettier-php** en local et vérifier que package.json contienne [le plugin](https://stackoverflow.com/a/63228491).

Vous pouvez l'installer sur vos machines et l'exécuter onSave ou utiliser la commande suivante avant de publier vos commits.

```bash
docker-compose exec php npx prettier --write .

# ou avec l'alias:
locopretty
```

## Envoi des courriels

Nous avons deux manières d'envoyer des courriels:

1. Via [Mailgun](https://www.mailgun.com/) quand les templates sont hébergés dans ce repertoire.

2. Via [Mandrill](https://mandrillapp.com/) quand les templates sont créés sur [Mailchimp](https://us18.admin.mailchimp.com/templates/) puis envoyer à Mandrill.

    - Lorsqu'un template est modifié sur Mailchimp, il doit être envoyé à Mandrill via le [bouton](https://us18.admin.mailchimp.com/templates/) "Send to Mandrill" puis publier via le bouton "[Publish](https://mandrillapp.com/templates/code?id=confirmation-d-inscription-sp-13-au-27-oct)" sur Mandrill.

## Kubernetes

Les fichiers de configuration permettant d'héberger l'application dans un cluster kubernetes sont dans le répertoire du même nom.

### Création de l'application

Commencer par remplir les fichiers `secret.yaml` et `configmap.yaml` (les infos "passport" seront remplies par la suite), puis par les créer.
> `kubectl apply -f kubernetes/configmap.yaml`  
> `kubectl apply -f kubernetes/secret.yaml`  

Installer ensuite les différents éléments:
> `kubectl apply -f kubernetes/database.yaml`  
> `kubectl apply -f kubernetes/storage.yaml`  
> `kubectl apply -f kubernetes/api.yaml`  
> `kubectl apply -f kubernetes/app.yaml`  

TODO: créer un conteneur createbuckets qui se lance une fois au démarrage pour initialiser le stockage

### Publication sur un nom de domaine

Pour que l'application soit accessible depuis l'extérieur, il faut installer ingress.
Voilà la procédure à suite pour OVH :
```
helm repo add ingress-nginx https://kubernetes.github.io/ingress-nginx
helm repo update
helm -n ingress-nginx install ingress-nginx ingress-nginx/ingress-nginx --create-namespace
```
On obtient alors une adresse IP publique vers laquelle il faut faire pointer le domaine
> `kubectl get ingress`  
```
NAME          CLASS   HOSTS               ADDRESS         PORTS     AGE  
ingress-app   nginx   dev.colocauto.org   57.128.40.124   80  
```


### certificat SSL

Pour faire fonctionner l'application en HTTPS, on installe cert-manager qui va utiliser Let's Encryppt pour créer et renouveller automatiquement un certificat SSL pour notre domaine.

Installer le certificat
> `kubectl apply -f https://github.com/jetstack/cert-manager/releases/download/v1.10.1/cert-manager.yaml`  
> `kubectl apply -f kubernetes/certificate.yaml`  

Attendre quelques minutes que le certificat soit créé. Pour suivre le processus :

```
kubectl logs -n cert-manager -lapp=cert-manager
```

## Attributions

Photos libres de droits:

* Photo de Théophile Devaux par [Brooke Cagle](https://unsplash.com/@brookecagle) sur [Unsplash](https://unsplash.com/fr/photos/JrzzESCqeko)
* Photo de Mathieu Arnaud par [Leilani Angel](https://unsplash.com/@leilaniangel) sur [Unsplash](https://unsplash.com/fr/photos/K84vnnzxmTQ)
  
  
