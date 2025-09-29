# Plataforma Symfony + Twig + JavaScript (PostgreSQL)

Proyecto generado el 2025-09-26 listo para correr en macOS, subir a GitHub y desplegar gratis.

## Requisitos
- PHP 8.3+ (ideal 8.4)
- Composer 2
- Symfony CLI (recomendado)
- PostgreSQL 15/16 (opcional; por defecto usamos SQLite para arrancar más rápido)

## Pasos rápidos (SQLite)
```bash
composer install
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n || true
symfony serve -d # o php -S localhost:8000 -t public
```

Abrir: http://localhost:8000

## Cambiar a PostgreSQL
En `.env`, usa:
```
# DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
DATABASE_URL="postgresql://usuario:password@127.0.0.1:5432/mi_bd?serverVersion=16&charset=utf8"
```
Luego:
```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n
```

## Crear usuario admin
```bash
php bin/console app:make-admin email@ejemplo.com
```

## Despliegue gratuito
- **Render**: Web Service (Start: `php -S 0.0.0.0:$PORT -t public`), Postgres add-on, `DATABASE_URL` configurada.
- **Railway**: conecta repo, añade Postgres, `DATABASE_URL` env var.
- **Fly.io**: opcional con Dockerfile.

## Rutas
- `/` listado de proyectos
- `/project/new` crear proyecto (requiere login)
- `/project/<built-in function id>` detalle + postulación
- `/my-projects` proyectos del usuario
- `/register` registro
- `/login` login, `/logout` logout

---
### Uso con PostgreSQL (rápido con Docker)
1. `docker compose up -d`
2. Copia `.env.postgres` a `.env` **(o** `.env.local.example` a `.env.local`)**
3. `composer install`
4. `php bin/console doctrine:migrations:migrate -n`
5. `symfony serve -d` y abre http://localhost:8000

> Si no usas Docker, instala Postgres local y ajusta `DATABASE_URL` con tus credenciales.


## Modo PostgreSQL (por defecto en este ZIP)
1) Levanta Postgres con Docker:
```bash
docker-compose up -d
```
2) Instala dependencias y crea esquema:
```bash
composer install --no-interaction
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n
```
3) Servir la app:
```bash
symfony serve -d
# o
php -S 0.0.0.0:8000 -t public
```

Credenciales DB usadas:
- host: 127.0.0.1
- port: 5432
- db: app_db
- user: app
- pass: app
