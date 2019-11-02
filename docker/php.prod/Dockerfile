FROM php:7.4.0RC4-fpm

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

RUN apt-get update && apt-get install -y libz-dev zip unzip git
