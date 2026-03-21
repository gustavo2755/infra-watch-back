# Infra Watch

Initial project base with PHP 8.3, MySQL 8, Docker, Composer and entry point in `public`.

## Requirements

- Docker
- Docker Compose

## Start environment

```bash
docker compose up --build -d
```

## Access application

- URL: `http://localhost:8000`

## Migrations and seeds

Run inside the container:

```bash
docker compose exec app php database/migrate.php
docker compose exec app php database/seed.php
```

To wipe the database and run migrations from scratch:

```bash
docker compose exec app php database/migrate.php fresh
docker compose exec app php database/seed.php
```

### Test credentials

| Field    | Value                |
|----------|----------------------|
| **Email** | `admin@infra.watch`   |
| **Password** | `password123`         |

To override: set `SEED_ADMIN_EMAIL` and `SEED_ADMIN_PASSWORD` in `.env`.

### Service checks (seeder)

Created automatically: Nginx (nginx), MySQL (mysql), Apache (apache2), PHP-FPM (php-fpm). No seeder for servers — create via `POST /api/servers`.

## Tests

Tests use **SQLite** in memory, isolated from the main MySQL database. The container includes `pdo_sqlite` (PHP base image). Always run **inside the container**:

```bash
docker compose exec app ./vendor/bin/phpunit
```

The test database is recreated from scratch on each test run. PHP, SQLite or other dependencies are not required on the host machine.

## Stop environment

```bash
docker compose down
```
