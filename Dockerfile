FROM php:8.4-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev libpq-dev git unzip \
 && docker-php-ext-install intl pdo pdo_pgsql opcache \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1

WORKDIR /var/www/html
RUN a2enmod rewrite

COPY composer.json composer.lock ./

RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader || \
    { composer clear-cache && composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader; }

COPY . /var/www/html
RUN composer install --no-dev --optimize-autoloader

EXPOSE 8080
CMD php -S 0.0.0.0:$PORT -t public
