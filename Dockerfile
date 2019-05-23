FROM composer:latest

RUN wget -O /usr/local/bin/phpunit https://phar.phpunit.de/phpunit-8.phar \
    && chmod +x /usr/local/bin/phpunit \
    && phpunit --version

RUN apk --update upgrade && apk add autoconf automake make gcc g++

RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_connect_back=on" >> /usr/local/etc/php/conf.d/xdebug.ini
