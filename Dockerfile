FROM ubuntu:20.04 as dev
WORKDIR /app

ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt install -y software-properties-common
RUN add-apt-repository -y ppa:ondrej/php
RUN apt-get update \
 && apt-get install -y \
   php7.3 \
   php7.3-dev \
   libmcrypt-dev \
   default-mysql-client \
   libmagickwand-dev \
   libzip-dev \
   libpq-dev \
   imagemagick \
   git \
   php7.3-imagick \
   php7.3-gd \
   php7.3-zip \
   php7.3-pgsql \
   php7.3-mbstring \
   php7.3-dom \
   php7.3-curl


RUN update-alternatives --set php /usr/bin/php7.3

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=1.10.20

ENV COMPOSER_MEMORY_LIMIT=-1

CMD bash -c "php artisan key:generate && \
             composer install && \
             php artisan migrate && \
             php artisan serve --host=0.0.0.0"

###################
FROM dev as prod
COPY . .
RUN composer install

CMD bash -c "php artisan key:generate && \
             php artisan migrate && \
             php artisan serve --host=0.0.0.0"
