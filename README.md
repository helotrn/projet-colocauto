Locomotion
==========

Application et API de Locomotion

Prérequis
---------

- \>= PHP 7.3
- Node 10
- Postgresql 11
- composer, npm

Configuration
-------------

### Création de la base de données

```
CREATE DATABASE locomotion;
CREATE USER locomotion WITH ENCRYPTED PASSWORD 'locomotion';
GRANT ALL PRIVILEGES ON DATABASE qs_api TO qs_api;
```

Installation
------------

- `composer install`
- Copier `.env.example` en `.env` et ajuster les valeurs
  - `HASHIDS_SALT`, une chaîne de longueur arbitraire
  - `APP_KEY`, une chaîne de 32 caractères, par ex.
    `cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1`
- `php artisan migrate --seed`
- `php artisan passport:install` et copier les valeurs d'ID et de secret du client par mot de
  passe aux clés `PASSWORD_CLIENT_*` correspondantes du fichier `.env`
- `php artisan migrate` (une fois et à chaque fois que le schéma change)

Développement
-------------

- `php -S localhost:8000 -t public` pour l'API

Tests
-----

- Une fois: `./vendor/bin/phpunit tests`

Déploiement
-----------

- `vendor/bin/dep deployer {staging,production}`
