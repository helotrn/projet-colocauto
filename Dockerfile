FROM php:7.4-apache as dev

# installing dependecies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    imagemagick \
    libmagickwand-dev \
    libzip-dev \
    git

# installing php extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    gd \
    zip

# installing imagick through pecl because it is not working with docker-php-ect-install
RUN pecl install imagick    
RUN docker-php-ext-enable imagick

# Giving a server name to stop a warning
RUN echo "ServerName laravel-app.local" >> /etc/apache2/apache2.conf

# setting the correct doc root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# enabling mod rewrite in apache
RUN a2enmod rewrite headers

# putting our own php.ini
COPY ./php.ini /usr/local/etc/php/php.ini

# installing composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

CMD bash -c "composer install && \
             ln -s -f $OAUTH_PRIVATE_PATH '/var/www/html/storage/oauth-private.key' && \
             ln -s -f $OAUTH_PUBLIC_PATH '/var/www/html/storage/oauth-public.key' && \
             php artisan migrate --force && \
             php artisan queue:work & \
             apache2-foreground"


###################
FROM dev as prod
COPY . /var/www/html/
RUN composer install

CMD bash -c "ln -s -f $OAUTH_PRIVATE_PATH '/var/www/html/storage/oauth-private.key' && \
             ln -s -f $OAUTH_PUBLIC_PATH '/var/www/html/storage/oauth-public.key' && \
             php artisan migrate --force && \
             php artisan queue:work & \
             apache2-foreground"
