FROM node:15 as dev
WORKDIR /app

CMD bash -c "npm install && npm run serve -- --host 0.0.0.0"

##############################
FROM dev as build

COPY . .

RUN npm install
RUN npm run build
#############################
FROM node:15-alpine as prod

WORKDIR /app

RUN npm i -g serve
COPY --from=build /app/dist/ /app

CMD serve -l tcp://0.0.0.0:80 -s /app
