#!/usr/bin/env sh
# Render entrypoint for the OhMyWash POS.
#
# - Substitutes $PORT into the nginx config.
# - Writes DB_SSL_CA (Aiven CA certificate, supplied as a Render secret)
#   to a file only when set, so local development without SSL keeps working.
# - Caches config/routes/views, runs migrations, then starts nginx + php-fpm.
# - NEVER runs demo seeders. Reference data (service catalogue, WELCOME10)
#   is seeded only by the explicit manual command documented in DEPLOY.md.
set -eu

PORT_VALUE="${PORT:-8080}"

# nginx config uses ${PORT:-8080}; render it to a concrete number.
sed -i "s/\${PORT:-8080}/${PORT_VALUE}/g" /etc/nginx/nginx.conf

# Aiven requires SSL (ssl-mode=REQUIRED). The CA certificate arrives as the
# DB_SSL_CA secret (PEM content). PDO needs a file path, so materialise it.
if [ -n "${DB_SSL_CA:-}" ]; then
    printf '%s' "$DB_SSL_CA" > /tmp/aiven-ca.pem
    chmod 600 /tmp/aiven-ca.pem
    export MYSQL_ATTR_SSL_CA=/tmp/aiven-ca.pem
fi

cd /var/www/html

# Wait briefly for MySQL so the first deploy does not race the database.
if [ -n "${DB_HOST:-}" ]; then
    echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306}..."
    ATTEMPTS=0
    until mysqladmin ping -h"$DB_HOST" -P"${DB_PORT:-3306}" -u"${DB_USERNAME:-root}" -p"${DB_PASSWORD:-}" --ssl --silent 2>/dev/null \
        || mysqladmin ping -h"$DB_HOST" -P"${DB_PORT:-3306}" -u"${DB_USERNAME:-root}" -p"${DB_PASSWORD:-}" --silent 2>/dev/null; do
        ATTEMPTS=$((ATTEMPTS + 1))
        if [ "$ATTEMPTS" -ge 30 ]; then
            echo "MySQL not reachable after 30 attempts; continuing anyway (migrate will surface the error)."
            break
        fi
        sleep 2
    done
fi

echo "Caching configuration..."
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

echo "Running migrations..."
php artisan migrate --force --no-interaction

echo "Starting supervisord (nginx + php-fpm) on port ${PORT_VALUE}..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
