FROM node:15 as dev
WORKDIR /app

CMD bash -c "npm i && npm run serve"

##############################
FROM dev as build

COPY . .

RUN npm i 
RUN npm run build
#############################
FROM node:15-alpine as prod

WORKDIR /app

RUN npm i -g serve
COPY --from=build /app/build/ /app

CMD serve -l tcp://0.0.0.0:80 -s /app
