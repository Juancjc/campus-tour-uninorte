#!/usr/bin/env sh
set -eu

cd /var/www/html

mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

if [ "${DB_CONNECTION:-pgsql}" = "pgsql" ]; then
    echo "Aguardando PostgreSQL em ${DB_HOST:-postgres}:${DB_PORT:-5432}..."
    until pg_isready -h "${DB_HOST:-postgres}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-campus_tour}" -d "${DB_DATABASE:-campus_tour}" >/dev/null 2>&1; do
        sleep 2
    done
fi

php artisan storage:link --force
rm -f bootstrap/cache/*.php
php artisan migrate --force
php artisan db:seed --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart || true

pm2 startOrReload ecosystem.config.cjs --update-env
php-fpm -D

exec nginx -g "daemon off;"
