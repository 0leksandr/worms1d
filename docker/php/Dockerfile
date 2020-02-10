FROM php:7.4.1-fpm
RUN apt-get update &&\
    apt-get upgrade -y &&\
    apt-get install -y git &&\
    curl -sS https://getcomposer.org/installer | php &&\
        mv composer.phar /usr/local/bin/composer &&\
    adduser --disabled-password --force-badname _www &&\
    pecl install mongodb &&\
        docker-php-ext-enable mongodb &&\
    apt-get install -y libzip-dev zip && \
        docker-php-ext-install zip &&\
    echo "done"
