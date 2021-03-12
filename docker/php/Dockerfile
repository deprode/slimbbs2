FROM php:7-fpm-alpine
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install mbstring
RUN apk add build-base
RUN apk add autoconf
RUN pecl install xdebug-2.7.2 \
    && docker-php-ext-enable xdebug