Locomotion
==========

Application et API de Locomotion

Prérequis
---------

- \>= PHP 7.3
- Node 10
- Postgresql 10
- composer, npm
- Redis

Configuration
-------------

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
postgres=# \q
```

Installation
------------

- `composer install`
- `php artisan key:generate`
- `php artisan migrate --seed`
- `php artisan passport:install` et copier les valeurs d'ID et de secret du client par mot de
  passe aux clés `PASSWORD_CLIENT_*` correspondantes du fichier `.env`
- `php artisan migrate` (une fois et à chaque fois que le schéma change)

Développement
-------------

- `php artisan serve`
- `yarn serve` dans `resources/app`

Tests
-----

Créez la base de données de test en suivant les mêmes instructions que la section Configuration
avec le nom `locomotion_test` .

Migrez la base de données de test comme la base de donnée
de développement : `php artisan migration --env testing` .

Ajustez les noms d'hôtes des bases de données, en particulier `postgres` et `redis` .

- `./vendor/bin/phpunit`
- `yarn lint` dans `resources/app`

Déploiement
-----------

- `vendor/bin/dep deployer {staging,production}`
