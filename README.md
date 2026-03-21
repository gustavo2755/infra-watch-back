# Infra Watch

Base inicial do projeto com PHP 8.3, MySQL 8, Docker, Composer e ponto de entrada em `public`.

## Requisitos

- Docker
- Docker Compose

## Subir ambiente

```bash
docker compose up --build -d
```

## Acessar aplicação

- URL: `http://localhost:8000`

## Migrations e seeds

Executar dentro do container:

```bash
docker compose exec app php database/migrate.php
docker compose exec app php database/seed.php
```

### Credenciais para teste

| Campo    | Valor                |
|----------|----------------------|
| **Email** | `admin@infra.watch`   |
| **Senha** | `password123`         |

Para sobrescrever: definir no `.env` as variáveis `SEED_ADMIN_EMAIL` e `SEED_ADMIN_PASSWORD`.

### Service checks (seeder)

São criados automaticamente: Nginx (nginx), MySQL (mysql), Apache (apache2), PHP-FPM (php-fpm). Não há seeder para servers — criar via `POST /api/servers`.

## Testes

Os testes usam **SQLite** em memória, isolados do banco MySQL principal. O container inclui `pdo_sqlite` (imagem base PHP). Rodar sempre **dentro do container**:

```bash
docker compose exec app ./vendor/bin/phpunit
```

O banco de testes é recriado do zero a cada execução da suíte. Não é necessário PHP, SQLite ou dependências instalados na máquina host.

## Derrubar ambiente

```bash
docker compose down
```
