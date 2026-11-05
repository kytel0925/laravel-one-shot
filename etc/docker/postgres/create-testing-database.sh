#!/usr/bin/env bash
# Creates the database the test suite uses (phpunit.xml sets DB_DATABASE),
# so `php artisan test` never resets the development database. Runs once, on
# a fresh volume; etc/bin/dev.sh creates it for older volumes.
set -euo pipefail

psql -v ON_ERROR_STOP=1 \
    --username "$POSTGRES_USER" \
    --dbname "$POSTGRES_DB" \
    --set=test_db="${POSTGRES_TEST_DB:-testing}" \
    --set=owner="$POSTGRES_USER" <<-'SQL'
    SELECT format('CREATE DATABASE %I OWNER %I', :'test_db', :'owner')
    WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = :'test_db')\gexec
SQL
