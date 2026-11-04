#!/usr/bin/env bash
# Creates the database the test suite uses (phpunit.xml sets DB_DATABASE),
# so `php artisan test` never resets the development database.
set -euo pipefail

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-SQL
    SELECT 'CREATE DATABASE ${POSTGRES_TEST_DB:-testing} OWNER "$POSTGRES_USER"'
    WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = '${POSTGRES_TEST_DB:-testing}')\gexec
SQL
