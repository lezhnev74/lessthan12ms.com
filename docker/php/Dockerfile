ARG PHP_IMAGE=php:8.4.27-cli
FROM ${PHP_IMAGE}

WORKDIR /var/www

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev zip unzip git \
    && git config --global --add safe.directory /var/www \
    && rm -rf /var/lib/apt/lists/*

ARG XDEBUG_VERSION=3.5.3
RUN mkdir -p /usr/src/php/ext/xdebug \
    && curl -fsSL "https://pecl.php.net/get/xdebug-${XDEBUG_VERSION}.tgz" | tar xz -C /usr/src/php/ext/xdebug --strip 1 \
    && docker-php-ext-install xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

ENV PHP_IDE_CONFIG="serverName=LessThan12msApp"

COPY php.ini /usr/local/etc/php/php.ini

EXPOSE 80
