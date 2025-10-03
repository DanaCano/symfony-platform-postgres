#!/usr/bin/env sh
set -eu

echo "=== BOOT: Symfony on Render ==="
echo "APP_ENV=${APP_ENV:-prod}  APP_DEBUG=${APP_DEBUG:-0}"
[ -n "${DATABASE_URL:-}" ] && echo "DATABASE_URL (redacted host): $(printf '%s\n' "$DATABASE_URL" | sed 's#://.*@#://***:***@#')"

export APP_ENV="${APP_ENV:-prod}"
export APP_DEBUG="${APP_DEBUG:-0}"

cd /var/www/html

if [ ! -f vendor/autoload.php ]; then
  echo "-> Installing composer dependencies (no-dev, no-scripts)..."
  composer install --no-dev --prefer-dist --no-interaction --no-scripts --optimize-autoloader
fi

echo "-> Waiting for database..."
for i in $(seq 1 60); do
  if php -d memory_limit=-1 bin/console doctrine:query:sql "SELECT 1" --no-interaction >/dev/null 2>&1; then
    echo "   Database is up."
    break
  fi
  sleep 2
done

echo "-> Ensure migration metadata table..."
php -d memory_limit=-1 bin/console doctrine:migrations:sync-metadata-storage -n || true

echo "-> Running migrations (with baseline fallback)..."
if ! php -d memory_limit=-1 bin/console doctrine:migrations:migrate -n --allow-no-migration; then
  echo "   Migrations failed (likely tables already exist). Marking existing migrations as executed..."
  php -d memory_limit=-1 bin/console doctrine:migrations:version --add --all -n || true
fi

echo "-> Validating Doctrine schema..."
php -d memory_limit=-1 bin/console doctrine:schema:validate || true

echo "-> Clearing and warming up cache..."
php -d memory_limit=-1 bin/console cache:clear --no-warmup || true
php -d memory_limit=-1 bin/console cache:warmup || true

echo "-> Installing assets..."
php -d memory_limit=-1 bin/console assets:install public --no-interaction || true

echo "-> Starting PHP built-in server..."
exec php -S 0.0.0.0:${PORT:-8080} -t public
