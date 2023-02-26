FROM php:8.1-fpm-alpine

ENV PHPGROUP=alireza
ENV PHPUSER=alireza

RUN apk update && apk add mysql-client

RUN adduser -g ${PHPGROUP} -s /bin/sh -D ${PHPUSER}

RUN sed -i "s/user = www-data/user = ${PHPUSER}/g" /usr/local/etc/php-fpm.d/www.conf
RUN sed -i "s/group = www-data/group = ${PHPGROUP}/g" /usr/local/etc/php-fpm.d/www.conf

RUN mv /usr/local/etc/php/php.ini-development "$PHP_INI_DIR/php.ini"
RUN sed -i "s/memory_limit = 128M/memory_limit = -1/g" /usr/local/etc/php/php.ini

RUN mkdir -p /var/www/html

RUN docker-php-ext-install pdo pdo_mysql exif bcmath sockets

#Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && php composer-setup.php --install-dir=/usr/local/bin --filename=composer

CMD ["php-fpm", "-y", "/usr/local/etc/php-fpm.conf", "-R"]
