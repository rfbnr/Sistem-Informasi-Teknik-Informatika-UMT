# ===== Base builder =====
FROM php:8.2-cli

# System deps & PHP extensions (termasuk Ghostscript)
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

# 1) Install vendor tanpa script (artisan belum ada)
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-progress --no-scripts

# 2) Copy source
COPY . .

# 3) Dump autoload & discover (artisan sudah ada)
RUN composer dump-autoload -o \
 && php artisan package:discover --ansi || true

# 4) Pastikan folder & permission benar, symlink storage dibuat saat build
RUN mkdir -p storage/app/public \
    && mkdir -p storage/app/temp \
    && mkdir -p public/storage \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && php artisan storage:link || true

# Default env (bisa override dari Railway Variables)
ENV APP_ENV=production \
    APP_DEBUG=false \
    PORT=8080

EXPOSE 8080

# 5) Entrypoint: robust .env bootstrap + optimisasi + serve
# - Jika .env tidak ada:
#     a) Jika .env.example ada -> copy
#     b) Jika tidak ada -> tulis .env minimal (APP_URL, FILESYSTEM_DISK, dst)
# - Jika APP_KEY env sudah ada -> skip key:generate
# - Jika belum ada -> generate
CMD sh -lc '\
  if [ ! -f .env ]; then \
    if [ -f .env.example ]; then \
      cp .env.example .env; \
    else \
      printf "APP_NAME=Laravel\nAPP_ENV=${APP_ENV}\nAPP_KEY=\nAPP_DEBUG=${APP_DEBUG}\nAPP_URL=${APP_URL:-http://localhost}\nLOG_CHANNEL=stack\nFILESYSTEM_DISK=public\n" > .env; \
    fi; \
  fi; \
  if [ -n "${APP_KEY}" ]; then \
    # inject APP_KEY dari env Railway jika diberikan
    if grep -q "^APP_KEY=" .env; then \
      sed -i "s#^APP_KEY=.*#APP_KEY=${APP_KEY}#g" .env; \
    else \
      printf "\nAPP_KEY=${APP_KEY}\n" >> .env; \
    fi; \
  fi; \
  if ! grep -q "^APP_KEY=base64:" .env; then \
    php artisan key:generate --force || true; \
  fi; \
  php artisan config:cache || true; \
  php artisan route:cache || true; \
  php artisan migrate --force || true; \
  php artisan serve --host=0.0.0.0 --port=${PORT} \
'


# # ===== Base builder =====
# FROM php:8.2-cli

# # System deps untuk ekstensi
# RUN apt-get update && apt-get install -y \
#     git unzip zip \
#     libzip-dev \
#     libpng-dev libjpeg-dev libfreetype6-dev \
#     libicu-dev \
#     ghostscript \
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
#     && php artisan package:discover --ansi || true

# # 4) Permission Laravel
# RUN mkdir -p storage bootstrap/cache \
#     && chmod -R ug+rwx storage bootstrap/cache \
#     && chown -R www-data:www-data storage bootstrap/cache \
#     && php artisan storage:link || true

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
