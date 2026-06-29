# erpKaan

ERP web application built on **CodeIgniter 4** (PHP). Server-side views + REST API.

## Stack

- **Framework:** CodeIgniter `v4.7.3` (installed via `codeigniter4/appstarter`)
- **PHP:** 8.5.2 (local). Extensions present: `intl`, `mbstring`, `json`, `mysqlnd`, `curl`, `gd`, `pdo_sqlite`.
- **Database:** MySQL/MariaDB (driver `MySQLi`), charset `utf8mb4`, collation `utf8mb4_unicode_ci`
- **Composer:** 2.6.5

## App type

Web + API: server-rendered CI4 views for the admin panel **plus** a REST API layer (e.g. for SPA/mobile clients later).

## Running locally

```bash
php spark serve --port 8080      # http://localhost:8080
```

Local DB (development): database `erpkaan`, user `root`. Test DB: `erpkaan_test`.
Config lives in `.env` (git-ignored — never commit credentials).

## ⚠️ Database privileges differ by environment — READ THIS

| Environment   | Privileges                                  |
|---------------|---------------------------------------------|
| development   | Full / universal (CREATE, ALTER, DROP, DELETE, etc.) |
| **production**| **Only `SELECT`, `INSERT`, `UPDATE`**       |

Production has **no `DELETE` and no DDL** (`CREATE`/`ALTER`/`DROP`). Implications for all code and schema work:

- **No hard deletes in prod.** Use **soft deletes** (CI4 Model `$useSoftDeletes = true`, `deleted_at` column). A soft delete is an `UPDATE`, so it works under prod privileges. Never call raw `DELETE`/`->delete()` paths that hit prod.
- **Migrations cannot run in production.** `php spark migrate` needs DDL. Schema changes are authored as migrations and run in **development only**; in prod they must be applied manually by a DBA with elevated privileges (or via a separate deploy step). Don't assume `spark migrate` runs on the prod box.
- Design every feature assuming prod can only read, add rows, and update rows. Anything that would need DELETE/DDL at runtime must be reworked.

## Key paths

- `app/Controllers/` — controllers
- `app/Models/` — models
- `app/Views/` — server-side views
- `app/Config/` — configuration (`Routes.php`, `Database.php`, etc.)
- `app/Database/Migrations/` — schema migrations (dev-authored)
- `public/` — web root (point the vhost here)
- `writable/` — logs, cache, sessions, uploads
- `tests/` — PHPUnit tests

## Conventions

_(to be filled in as the app grows)_

## Project log

- Bootstrapped CI4 appstarter, configured `.env` for development, generated encryption key, `git init`. Welcome page serves HTTP 200.
