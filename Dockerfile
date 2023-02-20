# syntax = docker/dockerfile:1.2
FROM php:8.0-cli-bullseye

RUN apt update
RUN apt install -y libyaml-dev libzip-dev unzip
RUN pecl channel-update pecl.php.net
RUN pecl install yaml zip && docker-php-ext-enable yaml
RUN pecl install pcov && docker-php-ext-enable pcov \
 && echo "pcov.enabled = 1" >> "$PHP_INI_DIR/php.ini" \
 && echo "pcov.directory = ." >> "$PHP_INI_DIR/php.ini"

COPY --from=composer:2.1.11 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app
CMD ["php"]

COPY [ "composer.json", "composer.lock", "/app/" ]
RUN composer check-platform-reqs --no-dev