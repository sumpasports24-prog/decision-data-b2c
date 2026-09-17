FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
# Se incluyen las dependencias de dev a propósito: la imagen se usa también
# para correr `php artisan test` dentro del contenedor (ver README).
RUN composer install --no-scripts --no-interaction --prefer-dist --no-progress

FROM php:8.3-cli-alpine

RUN apk add --no-cache icu-dev \
    && docker-php-ext-install pdo_mysql bcmath intl

WORKDIR /app

COPY --from=vendor /app/vendor ./vendor
COPY . .

COPY docker/entrypoint.sh /entrypoint.sh
COPY docker/entrypoint-worker.sh /entrypoint-worker.sh
RUN chmod +x /entrypoint.sh /entrypoint-worker.sh \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/framework/testing storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
