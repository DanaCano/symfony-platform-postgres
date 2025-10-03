#!/usr/bin/env sh
set -eu

echo "=== BOOT: Symfony on Render ==="
echo "APP_ENV=${APP_ENV:-not-set}  APP_DEBUG=${APP_DEBUG:-not-set}"
echo "DATABASE_URL (redacted host): $(printf '%s\n' "${DATABASE_URL:-missing}" | sed 's#://.*@#://***:***@#')"

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

echo "-> Creating database if not exists..."
php -d memory_limit=-1 bin/console doctrine:database:create --if-not-exists --no-interaction

HAS_MIGRATIONS="no"
if [ -d migrations ]; then
  if ls migrations/*.php >/dev/null 2>&1; then
    HAS_MIGRATIONS="yes"
  fi
fi

if [ "$HAS_MIGRATIONS" = "yes" ]; then
  echo "-> Running migrations..."
  php -d memory_limit=-1 bin/console doctrine:migrations:migrate -n --allow-no-migration
  echo "-> Sync metadata storage..."
  php -d memory_limit=-1 bin/console doctrine:migrations:sync-metadata-storage -n || true
else
  echo "-> No migrations found; applying schema:update --force..."
  php -d memory_limit=-1 bin/console doctrine:schema:update --force --no-interaction
  echo "-> Initialize migrations storage (so future migrations funcionen)..."
  php -d memory_limit=-1 bin/console doctrine:migrations:sync-metadata-storage -n || true
fi

echo "-> Validating Doctrine schema..."
php -d memory_limit=-1 bin/console doctrine:schema:validate || true

echo "-> Clearing and warming up cache..."
php -d memory_limit=-1 bin/console cache:clear --no-warmup || true
php -d memory_limit=-1 bin/console cache:warmup || true
php -d memory_limit=-1 bin/console assets:install public --no-interaction || true

echo "-> Starting PHP built-in server..."
exec php -S 0.0.0.0:${PORT:-8080} -t public
