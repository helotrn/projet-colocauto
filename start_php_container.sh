#/bin/bash

# putting the secrets at the laravel designated place
ln -s -f $OAUTH_PRIVATE_PATH '/var/www/html/storage/oauth-private.key' 
ln -s -f $OAUTH_PUBLIC_PATH '/var/www/html/storage/oauth-public.key' 

# migrate need the cache but the cache table need to exist first 
# so using the array cache for the migration
export CACHE_DRIVER_OLD=$CACHE_DRIVER 
export CACHE_DRIVER=array 
php artisan migrate --force 
export CACHE_DRIVER=$CACHE_DRIVER_OLD 

# starting the worker for the queue 
php artisan queue:work --tries=3 & \

# Finally starting apache
apache2-foreground
