#!/usr/bin/env bash
set -e

export APP_ENV="${APP_ENV:-prod}"
export APP_DEBUG="${APP_DEBUG:-0}"

if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
  echo "Installing composer dependencies..."
  composer install --no-dev --prefer-dist --no-interaction --no-scripts --optimize-autoloader
fi

echo "Waiting for database..."
for i in {1..60}; do
  if php -d memory_limit=-1 bin/console doctrine:query:sql "SELECT 1" --no-interaction >/dev/null 2>&1; then
    echo "Database is up."
    break
  fi
  sleep 2
done

echo "Running doctrine migrations (if any)..."
php -d memory_limit=-1 bin/console doctrine:migrations:migrate -n || true

echo "Validating Doctrine schema..."
php -d memory_limit=-1 bin/console doctrine:schema:validate || true

echo "Clearing and warming up cache..."
php -d memory_limit=-1 bin/console cache:clear --no-warmup || true
php -d memory_limit=-1 bin/console cache:warmup || true

echo "Installing assets to public/..."
php -d memory_limit=-1 bin/console assets:install public --no-interaction || true

echo "Starting PHP built-in server..."
exec php -S 0.0.0.0:${PORT:-8080} -t public
