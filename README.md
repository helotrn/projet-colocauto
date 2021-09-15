# LocoMotion

Application et API de LocoMotion

[![pipeline status](https://gitlab.com/Solon-collectif/locomotion.app/badges/master/pipeline.svg)](https://gitlab.com/Solon-collectif/locomotion.app/-/commits/master)
[![coverage report](https://gitlab.com/Solon-collectif/locomotion.app/badges/master/coverage.svg)](https://gitlab.com/Solon-collectif/locomotion.app/-/commits/master)

## Contribuer

Consultez le fichier [CONTRIBUTE.md](CONTRIBUTE.md)

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
-   emile@locomotion.app
-   ariane@locomotion.app
-   proprietairevoiture@locomotion.app
-   emprunteurvoiture@locomotion.app

Le mot de passe pour chacun des comptes est **locomotion**

## Tests

Créez la base de données de test en suivant les mêmes instructions que la section Configuration
avec le nom `locomotion_test` .

Migrez la base de données de test comme la base de donnée
de développement : `php artisan migrate --seed --env testing` .

Ajustez les noms d'hôtes des bases de données, en particulier `postgres` et `redis` .

-   `./vendor/bin/phpunit`
-   `npx prettier . --list-different`

## Déploiement

Pour déployer, il faut simplement avoir le tag correspondant à
l'environnement qui pointe vers le commit à déloyer.

Les tags suivants sont disponibles:

-   production
-   staging

Voici les étapes à suivre sur gitlab dans le section [tags](https://gitlab.com/solon-collectif/locomotion.app/-/tags):

-   effacer le tag que vous voulez déployer.
-   Créer un nouveau tag avec le même nom et l'associer à la branche que vous voulez déployer.

## Utilisation de prettier

Nous utilisons prettier pour le formattage du code. Vous devez l'installer sur vos machines.

Pour installer prettier et prettier-php:

```
sudo npm install --global prettier @prettier/plugin-php
```

Pour exécuter prettier et corriger les problèmes automatiquement, faire la commande à la racine:

```
npx prettier --write .
```
