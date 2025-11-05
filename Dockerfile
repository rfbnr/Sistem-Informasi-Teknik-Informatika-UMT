# ===== Base builder =====
FROM php:8.2-cli

# System deps & PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip zip \
    libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libicu-dev \
    ghostscript \
 && docker-php-ext-configure gd --with-jpeg --with-freetype \
 && docker-php-ext-install -j"$(nproc)" intl zip gd pdo_mysql opcache \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app

# 1) Install vendor deps without artisan available yet (no scripts)
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-progress --no-scripts

# 2) Copy project source
COPY . .

# 3) Generate autoload & discover packages (artisan already exists)
RUN composer dump-autoload -o \
 && php artisan package:discover --ansi || true

# 4) Ensure storage + public/storage exists and permission is correct
RUN mkdir -p storage/app/public \
    && mkdir -p storage/app/temp \
    && mkdir -p public/storage \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && php artisan storage:link || true

# Runtime config
ENV PORT=8080
EXPOSE 8080

# 5) Entrypoint
CMD sh -lc '\
  [ -f .env ] || cp .env.example .env || true; \
  php artisan key:generate --force || true; \
  php artisan config:cache || true; \
  php artisan route:cache || true; \
  php artisan migrate --force || true; \
  php artisan serve --host=0.0.0.0 --port=${PORT} \
'



# ===== Base builder =====
# FROM php:8.2-cli

# # System deps untuk ekstensi
# RUN apt-get update && apt-get install -y \
#     git unzip zip \
#     libzip-dev \
#     libpng-dev libjpeg-dev libfreetype6-dev \
#     libicu-dev \
#  && docker-php-ext-configure gd --with-jpeg --with-freetype \
#  && docker-php-ext-install -j"$(nproc)" intl zip gd pdo_mysql opcache \
#  && rm -rf /var/lib/apt/lists/*

# # Composer
# COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
# ENV COMPOSER_ALLOW_SUPERUSER=1

# WORKDIR /app

# # 1) Copy hanya file composer utk cache layer vendor
# COPY composer.json composer.lock ./
# # -> install tanpa scripts (belum ada artisan)
# RUN composer install --no-interaction --prefer-dist --no-progress --no-scripts

# # 2) Copy seluruh source
# COPY . .

# # 3) Sekarang artisan sudah ada -> aman untuk dump / discover
# RUN composer dump-autoload -o \
#  && php artisan package:discover --ansi || true

# # 4) Permission Laravel
# RUN mkdir -p storage bootstrap/cache \
#  && chmod -R ug+rwx storage bootstrap/cache

# # Runtime config
# ENV PORT=8080
# EXPOSE 8080

# # 5) Entrypoint: amankan first run & optimasi, baru serve
# CMD sh -lc '\
#   [ -f .env ] || cp .env.example .env || true; \
#   php artisan key:generate --force || true; \
#   php artisan storage:link || true; \
#   php artisan config:cache || true; \
#   php artisan route:cache || true; \
#   # jalankan migrate jika DB tersedia, kalau tidak, tetap jalan
#   php artisan migrate --force || true; \
#   php artisan serve --host=0.0.0.0 --port=${PORT} \
# '
