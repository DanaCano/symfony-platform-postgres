#!/usr/bin/env bash
set -e

echo "Waiting for database..."
for i in {1..60}; do
  if php bin/console doctrine:query:sql "SELECT 1" --no-interaction >/dev/null 2>&1; then
    echo "Database is up."
    break
  fi
  sleep 2
done

echo "Running doctrine migrations (if any)..."
php bin/console doctrine:migrations:migrate -n || true

exec php -S 0.0.0.0:${PORT:-8080} -t public
