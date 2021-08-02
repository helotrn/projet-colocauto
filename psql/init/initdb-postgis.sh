#!/bin/sh

psql -U locomotion -d locomotion -c "CREATE EXTENSION IF NOT EXISTS postgis;"
psql -U locomotion -d locomotion -c "CREATE EXTENSION IF NOT EXISTS postgis_topology;"
psql -U locomotion -d locomotion -c "CREATE EXTENSION IF NOT EXISTS fuzzystrmatch;"
psql -U locomotion -d locomotion -c "CREATE EXTENSION IF NOT EXISTS postgis_tiger_geocoder;"
psql -U locomotion -d locomotion -c "CREATE EXTENSION IF NOT EXISTS postgis_tiger_geocoder;"
psql -U locomotion -d locomotion -c "CREATE EXTENSION IF NOT EXISTS citext;"

