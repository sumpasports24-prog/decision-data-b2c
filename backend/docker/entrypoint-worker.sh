#!/bin/sh
set -e

# A propósito NO corre migrate/seed aquí: si worker y scheduler hicieran lo
# mismo que el entrypoint de "app" en paralelo, terminan compitiendo por
# las mismas tablas (nos pasó: migrate:fresh de un contenedor tumbaba la
# tabla que el seeder de otro estaba leyendo, y el contenedor moría sin
# nadie que lo reinicie). Solo "app" migra; este entrypoint solo espera a
# que la base sea alcanzable — `depends_on: app: condition: service_healthy`
# en docker-compose.yml ya garantiza que app terminó de migrar antes de que
# este contenedor arranque siquiera.

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q "^APP_KEY=base64" .env 2>/dev/null; then
    NUEVA_KEY="base64:$(head -c 32 /dev/urandom | base64)"
    if grep -q "^APP_KEY=" .env; then
        sed -i "s|^APP_KEY=.*|APP_KEY=${NUEVA_KEY}|" .env
    else
        echo "APP_KEY=${NUEVA_KEY}" >> .env
    fi
    export APP_KEY="${NUEVA_KEY}"
fi

until php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; do
    echo "Esperando a la base de datos..."
    sleep 2
done

exec "$@"
