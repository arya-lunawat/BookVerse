FROM php:8.2-apache
RUN docker-php-ext-install mysqli pdo pdo_mysql
COPY . /var/www/html/
WORKDIR /var/www/html
RUN php composer.phar install --no-dev --optimize-autoloader
RUN a2enmod rewrite
EXPOSE 80
CMD ["apache2-foreground"]
