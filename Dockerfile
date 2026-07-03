FROM php:8.2-apache
RUN apt-get update && apt-get install -y unzip git && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install mysqli pdo pdo_mysql
COPY . /var/www/html/
WORKDIR /var/www/html
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN php composer.phar install --no-dev --optimize-autoloader
RUN a2enmod rewrite
EXPOSE 80
CMD ["bash", "-lc", "set -e; a2dismod mpm_event mpm_worker || true; rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*; a2enmod mpm_prefork; apache2ctl -t; exec apache2-foreground"]