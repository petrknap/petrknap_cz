FROM php:7.0-apache

# Install database
RUN docker-php-ext-install pdo pdo_mysql

# Install ZIP
RUN apt-get update \
    && apt-get install -y zlib1g-dev \
    && docker-php-ext-install zip

# Configure server
COPY apache.conf /etc/apache2/sites-available/000-default.conf
COPY php.ini /usr/local/etc/php/php.ini
RUN a2enmod rewrite
RUN a2enmod headers
