FROM php:7.3-fpm

RUN docker-php-ext-install pdo_mysql

RUN groupadd dev -g 999
RUN useradd dev -g dev -d /home/dev -m
RUN usermod -a -G www-data dev

WORKDIR /www
