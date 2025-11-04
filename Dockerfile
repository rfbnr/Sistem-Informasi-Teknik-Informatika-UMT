FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git unzip zip \
    libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libicu-dev \
 && docker-php-ext-configure gd --with-jpeg --with-freetype \
 && docker-php-ext-install -j"$(nproc)" intl zip gd pdo_mysql opcache \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app
# copy semua dulu → artisan tersedia saat composer jalan
COPY . .

RUN composer install --no-interaction --prefer-dist --no-progress \
 && composer dump-autoload -o \
 && php artisan package:discover --ansi || true \
 && mkdir -p storage bootstrap/cache \
 && chmod -R ug+rwx storage bootstrap/cache

ENV PORT=8080
EXPOSE 8080
CMD sh -lc '\
  [ -f .env ] || cp .env.example .env || true; \
  php artisan key:generate --force || true; \
  php artisan storage:link || true; \
  php artisan config:cache || true; \
  php artisan route:cache || true; \
  php artisan migrate --force || true; \
  php artisan serve --host=0.0.0.0 --port=${PORT} \
'
