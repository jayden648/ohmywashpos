# Multi-stage build for the OhMyWash POS (Laravel 13 + Vite) on Render's
# free Docker runtime. Render has no native PHP runtime, so the final
# image carries nginx + php-fpm and listens on $PORT.
#
# Stage 1 (assets): compile Tailwind/Vite CSS+JS.
# Stage 2 (runtime): PHP 8.3 + nginx + php-fpm, pdo_mysql only. No pgsql.

# ---------------------------------------------------------------- assets
FROM node:22-alpine AS assets

WORKDIR /build

# Install JS dependencies first for a stable layer cache.
COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources/ resources/
COPY public/ public/

RUN npm run build

# --------------------------------------------------------------- runtime
FROM php:8.3-fpm-alpine AS runtime

# System packages: nginx + supervisor keep one process tree on Render free,
# mysql-client gives `mysqladmin ping` for the optional DB wait loop.
RUN apk add --no-cache \
    nginx \
    supervisor \
    mysql-client \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
        pcntl \
    && rm -rf /var/cache/apk/*

# Composer binary from the official image.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# PHP dependencies first, so code edits do not bust this layer.
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --prefer-dist \
        --optimize-autoloader

# Application code (see .dockerignore for what is excluded).
COPY . .

# Frontend assets compiled in the Node stage.
COPY --from=assets /build/public/build ./public/build

# One-time Laravel wiring that is safe at build time (no DB needed).
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative \
    && php artisan package:discover --ansi \
    && php artisan storage:link \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# nginx serves `public/`, php-fpm answers on a unix socket.
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php-fpm-www.conf /usr/local/etc/php-fpm.d/zz-render.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["entrypoint.sh"]
