FROM php:7.4.1-fpm
RUN apt-get update ;\
    apt-get upgrade -y
RUN apt-get install -y git
RUN curl -sS https://getcomposer.org/installer | php ;\
    mv composer.phar /usr/local/bin/composer

RUN adduser --disabled-password --force-badname _www

RUN pecl install mongodb
RUN docker-php-ext-enable mongodb
