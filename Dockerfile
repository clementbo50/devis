FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    libzip-dev \
    unzip \
    curl \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json ./

RUN composer install --no-dev --optimize-autoloader

COPY . /var/www/html

RUN mkdir -p /var/www/html/public/assets/uploads \
    && chown -R www-data:www-data /var/www/html/public/assets/uploads \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/public/assets/uploads

EXPOSE 9000
