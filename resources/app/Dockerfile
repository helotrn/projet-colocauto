FROM node:15 as dev
WORKDIR /app

CMD bash -c "npm ci && npm run serve -- --host 0.0.0.0"

##############################
FROM dev as build
ARG _VUE_APP_GOOGLE_MAPS_API_KEY
ARG _VUE_APP_BACKEND_URL
ARG _VUE_APP_STRIPE_KEY
ARG _VUE_APP_MATOMO_HOST
ARG _VUE_APP_MATOMO_SITEID

COPY . .

ENV VUE_APP_GOOGLE_MAPS_API_KEY ${_VUE_APP_GOOGLE_MAPS_API_KEY}
ENV VUE_APP_BACKEND_URL ${_VUE_APP_BACKEND_URL}
ENV VUE_APP_STRIPE_KEY ${_VUE_APP_STRIPE_KEY}
ENV VUE_APP_MATOMO_HOST ${_VUE_APP_MATOMO_HOST}
ENV VUE_APP_MATOMO_SITEID ${_VUE_APP_MATOMO_SITEID}

RUN npm ci
RUN npm run build
###############################
FROM nginx as prod

COPY --from=build /app/dist/ /usr/share/nginx/html
COPY nginx.conf.template /etc/nginx/templates/default.conf.template

