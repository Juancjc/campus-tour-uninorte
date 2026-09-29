FROM php:8.3-fpm-bookworm AS php-base

ENV APP_HOME=/var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        nginx \
        nodejs \
        npm \
        postgresql-client \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libpq-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath gd intl opcache pcntl pdo_pgsql pgsql zip \
    && npm install --global pm2 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM php-base AS php-dependencies

WORKDIR /app
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

FROM node:24-alpine AS frontend

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
COPY --from=php-dependencies /app/vendor ./vendor
RUN npm run build

FROM php-base AS runtime

WORKDIR ${APP_HOME}

COPY . .
COPY --from=php-dependencies /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-campus-tour.conf
COPY docker-entrypoint.sh /usr/local/bin/campus-tour-entrypoint

RUN chmod +x /usr/local/bin/campus-tour-entrypoint \
    && mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && rm -f bootstrap/cache/*.php \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

HEALTHCHECK --interval=20s --timeout=5s --start-period=45s --retries=5 CMD curl --fail http://127.0.0.1/health || exit 1

ENTRYPOINT ["campus-tour-entrypoint"]
