FROM php:7.4-apache as dev

# installing dependecies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    imagemagick \
    libmagickwand-dev \
    libzip-dev \
    git \
    npm

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
COPY ./php.ini ${PHP_INI_DIR}/conf.d/php.ini

# installing composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# prettier
RUN npm install --global prettier @prettier/plugin-php

# xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug
RUN echo "xdebug.start_with_request = yes" >> ${PHP_INI_DIR}/conf.d/php.ini
RUN echo "xdebug.mode = debug" >> ${PHP_INI_DIR}/conf.d/php.ini

CMD bash -c "composer install && \
             ./start_php_container.sh"


###################
FROM dev as prod

COPY . /var/www/html/

# We put it back to remove xdebug that is launched on the dev layer
COPY ./php.ini ${PHP_INI_DIR}/conf.d/php.ini


RUN composer install
RUN chown -R www-data.www-data /var/www/html/

CMD ./start_php_container.sh
