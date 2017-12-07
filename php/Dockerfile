FROM php:5.6.31-fpm

WORKDIR /blazemeter

RUN apt-get update \
    && apt-get install -y libssl-dev \
    && pecl install mongo \
    && docker-php-ext-enable mongo \
