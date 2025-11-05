#!/usr/bin/env bash
set -e

cd /app

# 1) Siapkan .env
if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    cp .env.example .env
  else
    cat > .env <<EOF
APP_NAME=Laravel
APP_ENV=${APP_ENV}
APP_KEY=
APP_DEBUG=${APP_DEBUG}
APP_URL=${APP_URL:-http://localhost}

LOG_CHANNEL=stack
FILESYSTEM_DISK=public

# DB (override via Railway)
DB_CONNECTION=mysql
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}
EOF
  fi
fi

# 2) Inject APP_KEY dari env kalau ada
if [ -n "${APP_KEY}" ]; then
  if grep -q "^APP_KEY=" .env; then
    sed -i "s#^APP_KEY=.*#APP_KEY=${APP_KEY}#g" .env
  else
    printf "\nAPP_KEY=${APP_KEY}\n" >> .env
  fi
fi

# 3) Generate key jika belum ada
grep -q "^APP_KEY=base64:" .env || php artisan key:generate --force || true

# 4) Pastikan symlink & permission OK
php artisan storage:link || true
chmod -R 775 storage bootstrap/cache || true
chown -R www-data:www-data storage bootstrap/cache || true

# 5) Cache config & routes
php artisan config:cache || true
php artisan route:cache || true

# 6) Migrate (tidak bikin crash kalau DB belum siap)
php artisan migrate --force || true

# 7) Jalanin supervisor (nginx + php-fpm)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
