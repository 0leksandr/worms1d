FROM php:7.4.2-fpm

RUN apt-get update
RUN apt-get install -y git
RUN curl -sS https://getcomposer.org/installer | php &&\
    mv composer.phar /usr/local/bin/composer
RUN pecl install mongodb &&\
    docker-php-ext-enable mongodb
RUN apt-get install -y libzip-dev zip && \
    docker-php-ext-install zip
RUN useradd -m _www
RUN echo "memory_limit=2G" > /usr/local/etc/php/php.ini

USER _www
