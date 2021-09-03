#!/bin/sh
ln -s -f $OAUTH_PRIVATE_PATH /app/storage/oauth-private.key
ln -s -f $OAUTH_PUBLIC_PATH /app/storage/oauth-public.key

composer install
php artisan migrate --force
php artisan queue:work &
php artisan serve --host=0.0.0.0
