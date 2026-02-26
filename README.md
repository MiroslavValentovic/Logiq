# Logiq — Timesheet / Work Tracker MVP

Jednoduchá webová aplikácia na evidencu pracovného času (timesheet), projekty a mesačné reporty s uzávierkou a exportom do PDF.

## Funkcie MVP

- **Projekty (CRUD)** — vlastné projekty s názvom, klientom a stavom (aktívny/neaktívny)
- **Work logs** — záznamy času (dátum, projekt, minúty, poznámka) s filtrom podľa mesiaca
- **Mesačné reporty** — náhľad, uzamknutie mesiaca (lock) a generovanie PDF; po lock nie je možné meniť work logy daného mesiaca
- **Admin** — pre používateľov s `is_admin`: správa používateľov, prehľad projektov a work logov (read-only)

## Požiadavky

- PHP 8.2+
- Composer
- Node.js & npm (pre Vite/Tailwind — Breeze)
- Docker a Docker Compose (lokálny vývoj) alebo PostgreSQL priamo

## Lokálny setup (Docker)

```bash
# Spustenie (prvýkrát stiahne image, nainštaluje závislosti a spustí migrácie)
docker compose up --build

# Aplikácia: http://localhost:8000
# PostgreSQL: localhost:5432 (user: laravel, password: secret, db: laravel)
```

Premenné `DB_*` sú v `docker-compose.yml`; `.env` môžeš mať rovnaké hodnoty (alebo compose ich prepíše). Frontend (Vite) spusti na hoste: `npm install && npm run dev`, prípadne `npm run build`.

## Lokálne bez Dockeru

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# Nastav v .env DB_HOST=127.0.0.1 (a heslá) ak beží PostgreSQL lokálne
php artisan migrate
npm run build
php artisan serve
```

## Konfigurácia `.env`

- **DB_*** — pripojenie na PostgreSQL (`DB_HOST=postgres` v Dockeri, `127.0.0.1` pri lokálnom PostgreSQL). V Dockeri sú hodnoty v `docker-compose.yml`.

Nikdy necommitujte `.env`.

## Nasadenie (Websupport / shared hosting)

- `php artisan storage:link` — odkaz na storage pre PDF
- `php artisan config:cache`, `route:cache`, `view:cache`
- Oprávnenia: `storage/`, `bootstrap/cache/` zapisovateľné
- Queue: ak sa PDF generuje cez job, nastav `QUEUE_CONNECTION=database` a cron pre `php artisan queue:work`

## Bezpečnosť

- Všetky POST/PUT/DELETE formuláre používajú `@csrf`
- Rate limiting na auth routách (Laravel/Breeze)
- Validácia vstupov cez Form Requests
- Policies pre projekty, work logy a reporty; admin middleware pre `/admin`

## Štruktúra

- **Etapa 0–1:** Laravel 12, Breeze (Blade), Tailwind, Docker, migrácie (users + is_admin, projects, work_logs, monthly_reports)
- **Etapa 2–3:** Modely, vzťahy, ProjectPolicy, WorkLogPolicy, MonthlyReportPolicy, MonthLockService, EnsureUserIsAdmin
- **Etapa 4–8:** Projects CRUD, Work logs CRUD, Reports (lock + PDF cez Dompdf), Admin sekcia (users CRUD, projects/work-logs prehľad)

Implementácia podľa `TODO.md`.
