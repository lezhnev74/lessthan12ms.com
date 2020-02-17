FROM php:7.4-fpm

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

RUN apt-get -y update \
    && apt-get install -y libz-dev zip unzip git cron \
    && rm -rf /var/lib/apt/lists/*

ADD php.ini /usr/local/etc/php
