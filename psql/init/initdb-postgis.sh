#!/bin/sh

psql -U locomotion -d locomotion -c "CREATE EXTENSION IF NOT EXISTS postgis;"
psql -U locomotion -d locomotion -c "CREATE EXTENSION IF NOT EXISTS unaccent;"
psql -U locomotion -d locomotion -c "CREATE EXTENSION IF NOT EXISTS citext;"

