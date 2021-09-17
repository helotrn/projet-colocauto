#!/bin/sh

psql -U locomotion -d locomotion -c "CREATE EXTENSION IF NOT EXISTS postgis;"
psql -U locomotion -d locomotion -c "CREATE EXTENSION IF NOT EXISTS unaccent;"
psql -U locomotion -d locomotion -c "CREATE EXTENSION IF NOT EXISTS citext;"

psql -U locomotion -c 'create database locomotion_test;'

psql -U locomotion -d locomotion_test -c "CREATE EXTENSION IF NOT EXISTS postgis;"
psql -U locomotion -d locomotion_test -c "CREATE EXTENSION IF NOT EXISTS unaccent;"
psql -U locomotion -d locomotion_test -c "CREATE EXTENSION IF NOT EXISTS citext;"
