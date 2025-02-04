FROM php:8.3-fpm

RUN apt-get update 

RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable pdo_mysql 

# RUN apt-get install -y ffmpeg 

# RUN apt install -y curl unzip 

# RUN curl -sS https://getcomposer.org/installer | php 

# RUN mv composer.phar /usr/local/bin/composer \
#     && composer require guzzlehttp/guzzle

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini 
