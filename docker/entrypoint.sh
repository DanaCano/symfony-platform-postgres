#!/usr/bin/env sh
set -e

export APP_ENV="${APP_ENV:-prod}"
export APP_DEBUG="${APP_DEBUG:-0}"

echo "Waiting for database..."
for i in $(seq 1 60); do
  if php -d memory_limit=-1 bin/console doctrine:query:sql "SELECT 1" --no-interaction >/dev/null 2>&1; then
    echo "Database is up."
    break
  fi
  sleep 2
done

echo "Updating Doctrine schema (creating tables if missing)..."
php -d memory_limit=-1 bin/console doctrine:schema:update --force --no-interaction || true

if [ -d "migrations" ]; then
  echo "Running migrations (if any)..."
  php -d memory_limit=-1 bin/console doctrine:migrations:migrate -n || true
fi

php -d memory_limit=-1 bin/console cache:clear --no-warmup || true
php -d memory_limit=-1 bin/console cache:warmup || true
php -d memory_limit=-1 bin/console assets:install public --no-interaction || true

exec php -S 0.0.0.0:${PORT:-8080} -t public