FROM php:7.3 as dev
WORKDIR /app

RUN apt-get update && apt-get install -y libmcrypt-dev \
    default-mysql-client libmagickwand-dev --no-install-recommends \
    git libzip-dev libpq-dev imagemagick \
    && pecl install imagick 

RUN docker-php-ext-enable imagick
RUN docker-php-ext-install gd zip pdo pdo_pgsql


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=1.10.20

ENV COMPOSER_MEMORY_LIMIT=-1

CMD bash -c "composer install && \
             php artisan key:generate && \
             php artisan migrate --seed && \
             php artisan passport:install && \
             php artisan migrate && \
             php artisan serve --host=0.0.0.0"