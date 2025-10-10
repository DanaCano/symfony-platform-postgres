# Plataform Symfony + Twig + JavaScript (PostgreSQL)

Projet prêt à être exécuté, téléchargé sur GitHub et déployé gratuitement (développement et améliorations en cours)

## Requirements
- PHP 8.3+ (ideally 8.4)
- Composer
- Symfony CLI (recommended)
- PostgreSQL 15/16 (facultatif ; par défaut, nous utilisons SQLite pour un démarrage plus rapide)

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
- `/` liste des projets
- `/project/new` créer un projet (connexion requise)
- `/project/<built-in function id>` détails + candidature
- `/my-projects` projets de l'utilisateur
- `/register` enregistrement
- `/login` connexion, `/logout` déconnexion


![Architecture](docs/architecture_flow.png)

![Flux](docs/uml_classes.png)

---
### Use with PostgreSQL (Quick with Docker)
1. `docker compose up -d`
2. Copy `.env.postgres` to `.env` **(o** `.env.local.example` a `.env.local`)**
3. `composer install`
4. `php bin/console doctrine:migrations:migrate -n`
5. `symfony serve -d` y abre http://localhost:8000

> Si vous n'utilisez pas Docker, installez Postgres localement et ajustez `DATABASE_URL` avec vos credentials.


## Mode PostgreSQL (par défaut dans ce fichier ZIP)
1) Set up Postgres with Docker:
```bash
docker-compose up -d
```
2) Installez les dépendances et créez le schéma :
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

## Déploiement de l'application 
Render est une plateforme d'hébergement PaaS (type Heroku) qui vous décharge de la gestion des serveurs : elle construit votre application à partir de GitHub, la déploie, l'expose avec HTTPS et vous fournit des services gérés tels que PostgreSQL, cron jobs, workers, etc.

Services dans Render : 
1) Service Web – symfony-platform-postgres
- Render clone votre dépôt GitHub et compile votre application (avec Dockerfile ou des commandes de compilation).
- Il lance un conteneur qui écoute sur un port (Render injecte PORT).
- Il vous attribue un domaine onrender.com avec TLS/SSL automatique.
- Vous pouvez définir des variables d'environnement (APP_ENV, APP_SECRET, DATABASE_URL, RECAPTCHA_SITE_KEY/SECRET, MAILER_DSN, etc.).
- Journaux accessibles dans le panneau ; contrôles de santé ; autoscale (selon le plan).

2) PostgreSQL – symfony-db
- Il s'agit d'une base de données gérée par Render.
- Render vous montre une chaîne de connexion (externe et interne).
