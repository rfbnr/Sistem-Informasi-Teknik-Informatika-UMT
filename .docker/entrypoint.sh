#!/usr/bin/env bash
set -e

cd /app

# 🔧 Reinitialize storage structure if missing (because of mounted volume)
mkdir -p \
  storage/app/public \
  storage/app/temp \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs

chmod -R 775 storage bootstrap/cache || true
chown -R www-data:www-data storage bootstrap/cache || true

# Pastikan symlink public/storage → storage/app/public
if [ ! -L "public/storage" ]; then
    ln -s ../storage/app/public public/storage || true
fi

touch storage/logs/queue.log
chmod 666 storage/logs/queue.log

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

# 2) Inject APP_KEY
if [ -n "${APP_KEY}" ]; then
  if grep -q "^APP_KEY=" .env; then
    sed -i "s#^APP_KEY=.*#APP_KEY=${APP_KEY}#g" .env
  else
    printf "\nAPP_KEY=${APP_KEY}\n" >> .env
  fi
fi

# 3) Generate APP_KEY if missing
grep -q "^APP_KEY=base64:" .env || php artisan key:generate --force || true

echo "🔧 Clearing old caches..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

php artisan storage:link || true

# 5) Cache config & routes
echo "🔐 Rebuilding caches..."
php artisan config:cache || true
php artisan route:cache || true

# 6) Run migrations (ignore errors if DB not ready yet)
echo "🔄 Running migrations..."
php artisan migrate --force || true

# 7) Start Supervisor
echo "🚀 Starting Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
