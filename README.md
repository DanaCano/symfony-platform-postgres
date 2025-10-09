# Plataform Symfony + Twig + JavaScript (PostgreSQL)

Project generated on 2025-09-26, ready to run on macOS, upload to GitHub, and deploy for free.

## Requirements
- PHP 8.3+ (ideally 8.4)
- Composer
- Symfony CLI (recommended)
- PostgreSQL 15/16 (optional; by default we use SQLite for faster startup)

## Quick steps (SQLite)
```bash
composer install
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n || true
symfony serve -d # o php -S localhost:8000 -t public
```

Open: http://localhost:8000

## Switch to PostgreSQL
In `.env`, use:
```
# DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
DATABASE_URL="postgresql://usuario:password@127.0.0.1:xxxx/mi_bd?serverVersion=16&charset=utf8"
```
Then:
```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n
```

## Create admin user
```bash
php bin/console app:make-admin email@example.com
```

## Free deployment
- **Render**: Web Service (Start: `php -S 0.0.0.0:$PORT -t public`), Postgres add-on, `DATABASE_URL` configured.
- **Railway**: connect repo, add Postgres, `DATABASE_URL` env var.
- **Fly.io**: optional with Dockerfile.

## Routes
- `/` listado de proyectos
- `/project/new` crear proyecto (requiere login)
- `/project/<built-in function id>` detalle + postulación
- `/my-projects` proyectos del usuario
- `/register` registro
- `/login` login, `/logout` logout

---
### Use with PostgreSQL (Quick with Docker)
1. `docker compose up -d`
2. Copy `.env.postgres` to `.env` **(o** `.env.local.example` a `.env.local`)**
3. `composer install`
4. `php bin/console doctrine:migrations:migrate -n`
5. `symfony serve -d` y abre http://localhost:8000

> If you are not using Docker, install Postgres locally and adjust `DATABASE_URL` with your credentials.


## Mode PostgreSQL (by default in this ZIP file)
1) Set up Postgres with Docker:
```bash
docker-compose up -d
```
2) Install dependencies and create schema:
```bash
composer install --no-interaction
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n
```
3) Serve the app:
```bash
symfony serve -d
# o
php -S 0.0.0.0:8000 -t public
```

DB credentials used:
- host: 127.0.0.1
- port: xxx
- db: app_db
- user: app
- pass: app
