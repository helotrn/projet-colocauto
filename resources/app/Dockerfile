FROM node:15 as dev
WORKDIR /app

CMD bash -c "npm install && npm run serve -- --host 0.0.0.0"

##############################
FROM dev as build

COPY . .

ENV VUE_APP_API_URL /api

RUN npm ci
RUN npm run build
###############################
FROM nginx as prod

COPY --from=build /app/dist/ /usr/share/nginx/html
COPY nginx.conf.template /etc/nginx/templates/default.conf.template

