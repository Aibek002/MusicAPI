FROM php:8.3-fpm

RUN apt-get update 
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable pdo_mysql \
    && apt-get install -y nginx \
    && apt-get install -y ffmpeg \ 
    && apt update && apt install -y curl unzip \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && composer require guzzlehttp/guzzle

 

COPY nginx/nginx.conf /etc/nginx/nginx.conf


COPY . /var/www/html
RUN nginx -t
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
CMD ["sh", "-c", "/usr/local/sbin/php-fpm && service nginx start"]

