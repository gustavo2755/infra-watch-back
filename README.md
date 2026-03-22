# Infra Watch

PHP 8.3 application with MySQL 8, Docker, Composer, and HTTP entry point in `public/`.

## Requirements

- Docker
- Docker Compose

## Starting the environment

```bash
docker compose up --build -d
```

## Accessing the application

- **URL:** `http://localhost:8000`

## Database: migrations and seeds

Run these **inside the `app` container** so PHP and DB settings match the stack:

```bash
docker compose exec app php database/migrate.php
docker compose exec app php database/seed.php
```

Reset the database (drops data, reapplies migrations):

```bash
docker compose exec app php database/migrate.php fresh
docker compose exec app php database/seed.php
```

### Default login (after seed)

| Field        | Value               |
| ------------ | ------------------- |
| **Email**    | `admin@admin.com`   |
| **Password** | `qweqwe`            |

Override via `.env`: `SEED_ADMIN_EMAIL`, `SEED_ADMIN_PASSWORD`.

### What `database/seed.php` creates

- **UserSeeder** — default admin (see table above).
- **ServiceCheckSeeder** — twelve named service checks (nginx, mysql, apache2, php-fpm, redis, memcached, postgresql, mongodb, elasticsearch, rabbitmq, certbot, prometheus).
- **Demo data** (runs after base seed) — twelve servers (`Seed Server 01`–`12`) with `last_check_at` set at seed time, links to service checks, and about ten monitoring logs per seed server. Logs use random **0–50%** resource metrics (useful for charts), aligned `checked_at` / `created_at` / `updated_at` per row, and multiple alert rows where `monitor_resources` is enabled.

**Idempotency:** running `database/seed.php` again skips servers that already exist by name and only **inserts** missing monitoring logs up to ten per seed server; it does not update existing rows. To refresh demo data, run `migrate.php fresh` and then `seed.php`.

**Tests:** `DatabaseSeeder` for PHPUnit loads only the base seed (user + service checks), not demo servers or logs, so tests stay isolated from demo data.

## Running tests

### What the test stack does

- PHPUnit is configured in [`phpunit.xml`](phpunit.xml). Tests use **SQLite in-memory** (`:memory:`), not Docker MySQL, so application data in the container DB is untouched.
- Each run applies **migrations** to a clean schema and seeds only what tests require. You do not need PHP or SQLite on the host if you run PHPUnit **inside the `app` container**.

### Prerequisites

1. Stack running: `docker compose up --build -d`
2. `vendor/` present (from the image build or `composer install`).

### Run the full suite (default)

```bash
docker compose exec app ./vendor/bin/phpunit
```

Equivalent to targeting every suite (e.g. `--testsuite All`).

### Run by suite

| Suite           | Command                                                                 | Focus |
| --------------- | ----------------------------------------------------------------------- | ----- |
| **All**         | `docker compose exec app ./vendor/bin/phpunit --testsuite All`          | Entire project |
| **Feature**     | `docker compose exec app ./vendor/bin/phpunit --testsuite Feature`      | HTTP/API, controllers, end-to-end behavior |
| **Integration** | `docker compose exec app ./vendor/bin/phpunit --testsuite Integration`  | Repositories, commands, services with SQLite |
| **Unit**        | `docker compose exec app ./vendor/bin/phpunit --testsuite Unit`         | Isolated code with mocks/stubs |

**Workflow tip:** run **Feature** often while changing APIs; use **Integration** for DB or queue changes; run **All** before pushing or large refactors.

### Single file or filtered run

```bash
docker compose exec app ./vendor/bin/phpunit tests/Integration/Services/QueueServiceTest.php
docker compose exec app ./vendor/bin/phpunit --filter RunMonitoringQueueCommandTest
```

### PHPUnit on the host (optional)

With PHP 8.3+, required extensions (e.g. `pdo_sqlite`), and local `vendor/`:

```bash
./vendor/bin/phpunit
```

Otherwise use the container commands.

## Monitoring queue (`bin/run-queue.php`)

In-process worker (not Redis/RabbitMQ). Each **cycle**:

1. Reads **eligible servers** from the database (see [`QueueService`](app/Services/QueueService.php), [`MonitoringQueueRepository`](app/Repositories/MonitoringQueueRepository.php)).
2. Enqueues them and **processes one server at a time** (service checks, optional metrics, persistence).

A **30-second cooldown** (default) avoids re-selecting a server immediately after a check. If nothing is due, you still get one cycle and output like `Processed 0 server(s)`.

### Single cycle (`--once`)

One cycle, then exit (no sleep between cycles):

```bash
docker compose exec app php bin/run-queue.php --once
```

Without Docker (uses your local PHP / `.env` / DB configuration):

```bash
php bin/run-queue.php --once
```

### Continuous mode

After each cycle, sleeps **30 seconds**, then repeats until you stop with Ctrl+C:

```bash
docker compose exec app php bin/run-queue.php
```

## Log cleanup (`bin/cleanup-logs.php`)

Removes monitoring logs older than each server’s `retention_days` (see [`CleanupOldLogsCommand`](app/Commands/CleanupOldLogsCommand.php)):

```bash
docker compose exec app php bin/cleanup-logs.php
```

## Monitoring API (JWT)

These routes require **Bearer JWT** authentication. Request/response details: [`docs/API_REFERENCE.md`](docs/API_REFERENCE.md).

| Method | Path |
| ------ | ---- |
| `GET`  | `/api/monitoring-logs` |
| `GET`  | `/api/monitoring-logs/{id}` |
| `GET`  | `/api/servers/{serverId}/monitoring-logs` |
| `GET`  | `/api/servers/{serverId}/monitoring-logs/dashboard` |

## Stopping the environment

```bash
docker compose down
```
