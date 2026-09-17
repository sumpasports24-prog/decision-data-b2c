#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q "^APP_KEY=base64" .env 2>/dev/null; then
    # No usamos `artisan key:generate`: si APP_KEY llega como variable de
    # entorno real (aunque vacía), ese comando se niega a escribir en .env
    # con "APP_KEY is already present in the environment". Se genera y se
    # escribe directo.
    NUEVA_KEY="base64:$(head -c 32 /dev/urandom | base64)"
    if grep -q "^APP_KEY=" .env; then
        sed -i "s|^APP_KEY=.*|APP_KEY=${NUEVA_KEY}|" .env
    else
        echo "APP_KEY=${NUEVA_KEY}" >> .env
    fi
    export APP_KEY="${NUEVA_KEY}"
fi

# Espera a MySQL: depends_on con healthcheck ya lo hace, esto es un colchón
# extra por si el healthcheck pasa antes de que MySQL acepte conexiones.
# (Ojo: `db:show`/`db:monitor` usan el helper Number::format, que requiere la
# extensión intl -no instalada aquí- y explota aunque la conexión sí sirva.)
until php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; do
    echo "Esperando a la base de datos..."
    sleep 2
done

php artisan migrate --force
php artisan db:seed --force

exec "$@"
