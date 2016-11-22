FROM php:7.0-apache
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite
RUN a2enmod headers
COPY apache.conf /etc/apache2/sites-available/000-default.conf
COPY php.ini /usr/local/etc/php/php.ini
