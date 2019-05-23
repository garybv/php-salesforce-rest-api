FROM composer:latest

RUN wget -O /usr/local/bin/phpunit https://phar.phpunit.de/phpunit-8.phar \
    && chmod +x /usr/local/bin/phpunit \
    && phpunit --version
