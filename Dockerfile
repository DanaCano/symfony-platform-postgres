FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev libpq-dev git unzip \
 && docker-php-ext-install intl pdo pdo_pgsql opcache \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_MEMORY_LIMIT=-1

WORKDIR /var/www/html
RUN a2enmod rewrite

COPY composer.json composer.lock* ./
RUN composer clear-cache || true \
 && composer config --global --no-plugins allow-plugins.symfony/flex true \
 && composer config --global --no-plugins allow-plugins.symfony/runtime true \
 && composer install --no-dev --prefer-dist --no-interaction --no-scripts --optimize-autoloader \
 || { composer clear-cache && composer install --no-dev --prefer-dist --no-interaction --no-scripts --optimize-autoloader; }

COPY . .

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080
CMD ["/entrypoint.sh"]