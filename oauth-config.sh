#!/bin/bash
echo -e $OAUTH_PRIVATE > storage/oauth-private.key
echo -e $OAUTH_PUBLIC > storage/oauth-public.key
php artisan migrate
php artisan serve --host=0.0.0.0