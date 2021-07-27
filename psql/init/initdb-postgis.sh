#!/bin/sh

psql -U locomotion -d locomotion -c "CREATE EXTENSION IF NOT EXISTS postgis;"
psql -U locomotion -d locomotion -c "CREATE EXTENSION IF NOT EXISTS CREATE EXTENSION unaccent;"

