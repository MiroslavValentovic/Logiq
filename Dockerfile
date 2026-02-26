FROM php:8.2-cli-alpine

RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    libxml2-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    mbstring \
    xml \
    zip \
    bcmath \
    opcache \
    && docker-php-ext-enable opcache

# OPcache aj pre CLI (artisan serve) = menej I/O pri opakovaných requestoch
RUN echo "opcache.enable_cli=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 8000

# Pri štarte: composer install, migrácie, potom php artisan serve (kód je namountovaný z hosta)
CMD ["sh", "-c", "composer install --no-interaction && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000"]
