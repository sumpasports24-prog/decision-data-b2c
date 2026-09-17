FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
# Se incluyen las dependencias de dev a propósito: la imagen se usa también
# para correr `php artisan test` dentro del contenedor (ver README).
RUN composer install --no-scripts --no-interaction --prefer-dist --no-progress

FROM php:8.3-cli-alpine

RUN docker-php-ext-install pdo_mysql bcmath

WORKDIR /app

COPY --from=vendor /app/vendor ./vendor
COPY . .

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh \
    && mkdir -p storage/framework/{cache,sessions,views,testing} storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
