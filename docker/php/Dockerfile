FROM php:7.4

WORKDIR /var/www

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

RUN apt-get update &&\
    apt-get install -y libz-dev zip unzip git

# Install PECL extensions
ARG XDEBUG_VERSION=2.8.0

RUN mkdir -p /usr/src/php/ext/xdebug && \
    curl -fsSL https://xdebug.org/files/xdebug-${XDEBUG_VERSION}.tgz | tar xz -C /usr/src/php/ext/xdebug --strip 1 && \
    docker-php-ext-install xdebug

# This needs in order to run xdebug from PhpStorm
ENV PHP_IDE_CONFIG "serverName=LessThan12msApp"

ADD php.ini /usr/local/etc/php

EXPOSE 80