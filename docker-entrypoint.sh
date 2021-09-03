#!/bin/sh
sleep 1
cp -f $OAUTH_PRIVATE_PATH /app/storage/oauth-private.key
cp -f $OAUTH_PUBLIC_PATH /app/storage/oauth-public.key

php artisan migrate --force
php artisan queue:work & 
