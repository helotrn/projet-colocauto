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

## Initialisation de la base de données et de MinIO

Une fois que l'application est démarrée,
dans un autre terminal, faire:

-   `docker-compose exec php php artisan migrate --seed`

### Se connecter à l'application

Les comptes suivants auront été créés:

-   soutien@locomotion.app (admin)
-   solonahuntsic@locomotion.app
-   solonpetitepatrie@locomotion.app
-   emprunteurahuntsic@locomotion.app
-   proprietaireahuntsic@locomotion.app
-   emprunteurpetitepatrie@locomotion.app
-   proprietairepetitepatrie@locomotion.app

Le mot de passe pour chacun des comptes est **locomotion**

## Tests

Tests au complet, exécuter:

`docker-compose exec php bash ./phpunit`

:warning: à exécuter avant de soumettre votre MR.

Pour tester une fraction des tests localement, 

`docker-compose exec php bash ./phpunit --filter ReviewableTest`

Notez que `php artisan test` n'est pas une commande valide avec notre configuration.

Les variables dans l'environnement de test se trouvent dans: `.env.testing` ou `phpunit.xml`.

Pour en savoir plus, [voici un article utile](https://mattstauffer.com/blog/environment-specific-variables-in-laravels-testing-environment).

## Déploiement

Pour déployer, il faut simplement avoir le tag correspondant à
l'environnement qui pointe vers le commit à déployer.

Les tags suivants sont disponibles:

-   production
-   staging

Voici les étapes à suivre sur gitlab dans le section [tags](https://gitlab.com/solon-collectif/locomotion.app/-/tags):

-   effacer le tag que vous voulez déployer.
-   Créer un nouveau tag avec le même nom et l'associer à la branche que vous voulez déployer.

## Utilisation de prettier

Nous utilisons [prettier](https://prettier.io/) et [prettier-php](https://github.com/prettier/plugin-php) pour le formattage du code.

Pour VM Code, vous devez installer **prettier-php** en local et vérifier que package.json contienne [le plugin](https://stackoverflow.com/a/63228491).

Vous pouvez l'installer sur vos machines et l'exécuter onSave ou utiliser la commande suivante avant de publier vos commits.

```
docker-compose exec php npx prettier --write .
```

## Envoi des courriels

Nous avons deux manières d'envoyer des courriels:

1. Via [Mailgun](https://www.mailgun.com/) quand les templates sont hébergés dans ce repertoire.

2. Via [Mandrill](https://mandrillapp.com/) quand les templates sont créés sur [Mailchimp](https://us18.admin.mailchimp.com/templates/) puis envoyer à Mandrill.

    - Lorsqu'un template est modifié sur Mailchimp, il doit être envoyé à Mandrill via le [bouton](https://us18.admin.mailchimp.com/templates/) "Send to Mandrill" puis publier via le bouton "[Publish](https://mandrillapp.com/templates/code?id=confirmation-d-inscription-sp-13-au-27-oct)" sur Mandrill.