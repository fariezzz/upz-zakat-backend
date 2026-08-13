#!/bin/sh
set -e

echo "==> Starting application deployment setup..."

# Ensure storage directories exist and have proper permissions
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Run migrations if configured
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "==> Running database migrations..."
    php artisan migrate --force
fi

# Run seeders if explicitly set (optional for initial deployment)
if [ "$RUN_SEEDER" = "true" ]; then
    echo "==> Running database seeders..."
    php artisan db:seed --force
fi

# Optimize Laravel cache
echo "==> Caching configuration & routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Substitute PORT in Nginx configuration template
PORT="${PORT:-8080}"
echo "==> Configuring Nginx to listen on port ${PORT}..."
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf

echo "==> Starting PHP-FPM and Nginx via Supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
