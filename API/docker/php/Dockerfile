FROM php:apache
RUN docker-php-ext-install mysqli pdo pdo_mysql
FROM php:apache
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN apt-get update \
    && apt-get install -y apache2 \
    && a2enmod rewrite
