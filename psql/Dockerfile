FROM postgres:12

RUN apt-get update \
      && apt-cache showpkg \
      && apt-get install -y --no-install-recommends \
           postgresql-12-postgis-3 \
           postgresql-12-postgis-3-scripts \
           openssh-client \
      && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /docker-entrypoint-initdb.d

COPY ./init/initdb-postgis.sh /docker-entrypoint-initdb.d/postgis.sh
