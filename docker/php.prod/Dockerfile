ARG PHP_IMAGE=php:8.4.27-fpm
FROM ${PHP_IMAGE}

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev zip unzip git cron \
    && git config --global --add safe.directory /var/www \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

COPY php.ini /usr/local/etc/php/php.ini
