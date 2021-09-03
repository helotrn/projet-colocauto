FROM webdevops/php-nginx:7.3 as dev
WORKDIR /app

ENV WEB_DOCUMENT_ROOT /app/public

COPY ./docker-entrypoint.sh /entrypoint.d/
RUN echo chmod -R 1777 /var/log  >> /opt/docker/provision/entrypoint.d/05-permissions.sh
RUN echo chmod -R 1777 /var/lib/nginx/logs  >> /opt/docker/provision/entrypoint.d/05-permissions.sh


###################
FROM dev as prod
COPY . .
RUN composer install
