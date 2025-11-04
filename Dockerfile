# ===== Build stage =====
FROM php:8.2-cli AS build

# System deps for PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip zip \
    libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libicu-dev \
 && docker-php-ext-configure gd --with-jpeg --with-freetype \
 && docker-php-ext-install -j$(nproc) intl zip gd \
 && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Copy the rest of the app
COPY . .

# Cache/optimize (safe even if not all commands apply)
RUN php artisan config:clear || true \
 && php artisan route:clear || true \
 && php artisan view:clear || true

# ===== Runtime stage =====
FROM php:8.2-cli

# Copy built app and required PHP extensions (same base image -> extensions already present)
WORKDIR /app
COPY --from=build /app /app

# Laravel needs a port; Railway provides $PORT
ENV PORT=8080
EXPOSE 8080

# Generate key on first run if missing; then serve
CMD [ "sh", "-lc", "\
    [ -f .env ] || cp .env.example .env; \
    php -r \"file_exists('.env') && !str_contains(file_get_contents('.env'),'APP_KEY=base64') ? 0 : 0;\"; \
    php artisan key:generate --force || true; \
    php artisan config:cache || true; \
    php artisan route:cache || true; \
    php -S 0.0.0.0:${PORT} -t public \
" ]
