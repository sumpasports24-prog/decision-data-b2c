#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q "^APP_KEY=base64" .env 2>/dev/null; then
    php artisan key:generate --force --ansi
fi

# Espera a MySQL: depends_on con healthcheck ya lo hace, esto es un colchón
# extra por si el healthcheck pasa antes de que MySQL acepte conexiones.
until php artisan db:show > /dev/null 2>&1; do
    echo "Esperando a la base de datos..."
    sleep 2
done

php artisan migrate --force
php artisan db:seed --force

exec "$@"
