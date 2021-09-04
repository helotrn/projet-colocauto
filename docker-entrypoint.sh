#!/bin/sh
ln -s -f $OAUTH_PRIVATE_PATH /app/storage/oauth-private.key
ln -s -f $OAUTH_PUBLIC_PATH /app/storage/oauth-public.key
chown -h 1000.1000 /app/storage/*key

chown -R 1000.1000 /app/storage

php artisan migrate --force
php artisan queue:work & 
