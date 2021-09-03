FROM webdevops/php-nginx:7.3 as dev
WORKDIR /app

ENV WEB_DOCUMENT_ROOT /app/public

COPY ./docker-entrypoint.sh /entrypoint.d/

###################
FROM dev as prod
COPY . .
RUN composer install
