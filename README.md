# LocoMotion

Application et API de LocoMotion
Ce document est encore incomplet et en cours de rédaction par l'équipe LocoMotion !

[![pipeline status](https://gitlab.com/Solon-collectif/locomotion.app/badges/master/pipeline.svg)](https://gitlab.com/Solon-collectif/locomotion.app/-/commits/master)
[![coverage report](https://gitlab.com/Solon-collectif/locomotion.app/badges/master/coverage.svg)](https://gitlab.com/Solon-collectif/locomotion.app/-/commits/master)

## Contribuer

Consultez le fichier [CONTRIBUTE.md](CONTRIBUTE.md)

## Prérequis

-   PHP 7.3
-   Node 10
-   Postgresql 10
    -   postgis
    -   unaccent
-   composer, yarn
-   Redis

## Configuration

Créez l'utilisateur :

```
locomotion@localhost $ createuser --interactive --pwprompt
```

Utilisez par exemple `locomotion:locomotion` , les valeurs par défaut sont dans `.env.example` .

Créez la base de données :

```
locomotion@localhost $ psql
postgres=# CREATE DATABASE locomotion OWNER locomotion;
CREATE DATABASE
postgres=# \q
locomotion@localhost $ psql locomotion
postgres=# CREATE EXTENSION postgis;
CREATE EXTENSION
postgres=# CREATE EXTENSION unaccent;
CREATE EXTENSION
postgres=# \q
```

## Installation

-   `composer install`
-   `php artisan key:generate`
-   `php artisan migrate --seed`
-   `php artisan passport:install` et copier les valeurs d'ID et de secret du client par mot de
    passe aux clés `PASSWORD_CLIENT_*` correspondantes du fichier `.env`
-   `php artisan migrate` (une fois et à chaque fois que le schéma change)

## Développement

-   `php artisan serve`
-   `yarn serve` dans `resources/app`

## Tests

Créez la base de données de test en suivant les mêmes instructions que la section Configuration
avec le nom `locomotion_test` .

Migrez la base de données de test comme la base de donnée
de développement : `php artisan migration --env testing` .

Ajustez les noms d'hôtes des bases de données, en particulier `postgres` et `redis` .

-   `./vendor/bin/phpunit`
-   `yarn lint` dans `resources/app`

## Déploiement

Si l'interface publique a été mise à jour, il peut être pertinent d'augmenter le numéro de version
dans `resources/app/.release` et de créer une version sur Gitlab. Le fichier `.release` ne doit
contenir qu'une seule ligne commençant par `VUE_APP_RELEASE`.

-   `vendor/bin/dep deployer {staging,production}`
