FROM node:15 as dev
WORKDIR /app

CMD bash -c "npm ci && npm run serve -- --host 0.0.0.0"

##############################
FROM dev as build
ARG _VUE_APP_GOOGLE_MAPS_API_KEY
ARG _VUE_APP_BACKEND_URL
ARG _VUE_APP_STRIPE_KEY

COPY . .

ENV VUE_APP_API_URL /api
ENV VUE_APP_GOOGLE_MAPS_API_KEY ${_VUE_APP_GOOGLE_MAPS_API_KEY}
ENV VUE_APP_BACKEND_URL ${_VUE_APP_BACKEND_URL}
ENV VUE_APP_STRIPE_KEY ${__VUE_APP_STRIPE_KEY}

RUN npm ci
RUN npm run build
###############################
FROM nginx as prod

COPY --from=build /app/dist/ /usr/share/nginx/html
COPY nginx.conf.template /etc/nginx/templates/default.conf.template

