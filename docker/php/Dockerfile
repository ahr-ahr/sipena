# ========================
# Frontend build
FROM node:20-alpine AS frontend

WORKDIR /app
COPY src/package.json src/package-lock.json ./
RUN npm install
COPY src .
RUN npm run build


# ========================
# PHP deps build
FROM php:8.4-cli AS builder

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libxml2-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    fileinfo \
    zip \
    pdo \
    pdo_mysql \
    intl \
    pcntl \
    gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY src ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ========================
# Runtime
FROM php:8.4-fpm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libxml2-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    fileinfo \
    zip \
    pdo \
    pdo_mysql \
    intl \
    pcntl \
    gd

RUN pecl install redis \
 && docker-php-ext-enable redis

COPY --from=builder /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY src ./

RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

USER www-data
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
