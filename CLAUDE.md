# erpKaan

**Domain:** condominium / HOA administration platform ("Kaan"). Web + REST API
built on **CodeIgniter 4** (PHP). Multi-tenant (manages **multiple condominios**).
Country: **México** (CFDI/SAT invoicing, IVA, currency MXN).

## What the product does

Resident-facing WebApp + admin back office for condominium management. Core actors
(roles): `superadmin`, `admin`, `comite` (board), `caseta` (gate/security),
`dueno` (owner), `inquilino` (tenant), `huesped` (temporary guest).

Roadmap (phased):

| Phase | Scope |
|---|---|
| **F1 — Foundations** | Auth + roles, Properties (condominios → torres → casas → cajones), People (dueños/inquilinos), Occupancy (uso propio / renta lineal / renta vacacional) |
| **F2 — Access control** | Resident WebApp, visit QR codes, parcels/delivery, gate (caseta) panel, push/email notifications on arrival/exit |
| **F3 — Finance** | Maintenance dues, late-fees/morosidad, multas, account statements, online payments, CFDI invoicing |
| **F4 — Amenities + comms** | Configurable common-area bookings (with/without cost), announcements, complaint tickets, polls/e-voting |
| **F5 — Vacation rental** | Guest registration, requirements, check-in/out, channel-manager integrations |

Notification channels (decided): **Push (PWA)** + **Email**. (WhatsApp/SMS deferred.)

Key domain rules:
- A **dueño** can own several casas (and a casa can have co-owners).
- A **persona** can be both owner and tenant — single unified `personas` registry.
- Each casa's use is **propio / renta_lineal / renta_vacacional**, tracked over time via occupancy records.
- Tenants (`ocupantes`) have one **principal** + N **secundarios**, each with contact + photo.
- Each casa has N parking spots (`cajones`); some cajones are visitor/common.

> ⚠️ Because **production cannot run DDL** (see DB section), front-load columns you
> know you'll need (e.g. CFDI fields) when authoring schema — adding a column later
> means a dev migration that a DBA must apply manually on prod.

## Stack

- **Framework:** CodeIgniter `v4.7.3` (installed via `codeigniter4/appstarter`)
- **PHP:** 8.5.2 (local). Extensions present: `intl`, `mbstring`, `json`, `mysqlnd`, `curl`, `gd`, `pdo_sqlite`.
- **Database:** MySQL/MariaDB (driver `MySQLi`), charset `utf8mb4`, collation `utf8mb4_unicode_ci`
- **Composer:** 2.6.5

## App type

Web + API: server-rendered CI4 views for the admin panel **plus** a REST API layer
consumed by the resident WebApp/PWA (and future mobile). Auth: **CodeIgniter Shield**
(planned) — session login for web + access tokens for API, with groups as roles.

## Running locally

Served by **MAMP PRO** (Apache) on **http://localhost:8888**. The MAMP host's
Document root must point at `.../erpKaan/public` (not the project root, or the
`.env` and source become web-accessible). `app.baseURL` in `.env` = `http://localhost:8888/`.

Alternative (built-in server): `php spark serve --port 8080`.

MAMP MySQL note: local MySQL listens on port **8889** with socket
`/Applications/MAMP/tmp/mysql/mysql.sock` (only relevant if connecting to a local DB).

## Database connection strategy

The app **always uses the `default` connection group**, pointing at ONE remote MySQL
DB (`cycoasis_kaan` @ `65.99.205.188`). **There is no separate dev database — local
development connects to the SAME database as production.** Only the connecting *user*
(and thus the privileges) differs per machine's `.env`:

| Where                       | `database.default.*` user | Privileges                  |
|-----------------------------|---------------------------|-----------------------------|
| Local dev (`.env` here)     | full-privilege dev user   | universal (CREATE/ALTER/DROP/DELETE) |
| Production server's `.env`  | `cycoasis_apirest_adm`    | SELECT/INSERT/UPDATE only   |

### ⚠️ Migrations from local hit the PRODUCTION schema directly

Because local and prod share the same schema, running `spark migrate` (or any DDL)
from a dev machine **alters the live production database in place**. The app's prod
user can't do DDL, but the dev user can — so schema evolution happens by a developer
migrating locally against the shared DB. Implications:
- Be deliberate: a migration here = a change to real prod data/schema. No throwaway "dev DB".
- Still use **soft deletes** and **front-load known-future columns** — runtime prod code only has SELECT/INSERT/UPDATE.
- Test/experimental schema work should use the `tests` group (a separate DB), not `default`.

Code never switches groups. `.env` is git-ignored — never commit credentials.

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

- **Auth:** CodeIgniter Shield. Roles = Shield groups, defined in `app/Config/AuthGroups.php`
  (`superadmin`, `admin`, `comite`, `caseta`, `dueno`, `inquilino`, `huesped`) with a
  permissions matrix. Default group for new users: `inquilino`.
- **Tables:** Spanish names (`condominios`, `torres`, `casas`, `cajones`, `personas`,
  `ocupaciones`, `ocupantes`, `casa_propietarios`, `vehiculos`). Shield tables stay English.
- **Every domain table** has `created_at`/`updated_at`/`deleted_at` (soft deletes), InnoDB.
- **FKs** use `RESTRICT`/`SET NULL` on delete (never `CASCADE` delete) — deletes are logical.
- **Multi-tenant:** domain rows carry `condominio_id`. Shield users are scoped to condominios
  via `condominio_usuarios`; residents via `personas.condominio_id`.
- Migrations run with `php spark migrate --all` (includes Shield/Settings vendor namespaces).

## Seed / first login

`php spark db:seed InitialSetupSeeder` creates a `superadmin` user
(`admin@erpkaan.mx` / `Kaan!2026Admin` — **change after first login**) and a demo condominio.

## Project log

- Bootstrapped CI4 appstarter, configured `.env`, generated encryption key, `git init`. Welcome page HTTP 200.
- Pointed MAMP docroot at `public/`; app at http://localhost:8888, `.env` no longer web-exposed.
- Connected `default` group to remote DB `cycoasis_kaan` (shared dev/prod schema, MariaDB 11.4).
- **F1 schema done:** installed Shield (auth + roles), authored 10 domain migrations
  (condominios → torres → casas → cajones → personas → casa_propietarios → ocupaciones →
  ocupantes → vehiculos, + condominio_usuarios), migrated against the live DB (19 tables),
  seeded superadmin + demo condominio.
- **Auth flow live:** `Home` redirects to `/dashboard` or `/login`; `/dashboard` (filter
  `session`) shows a role-aware module grid (cards gated by `user->can(permission)`).
  Login redirect → `/dashboard`. Set `App::$indexPage=''` for clean URLs. Verified
  end-to-end with the superadmin (login 303 → dashboard, all 10 modules visible).
- **Multi-tenancy = Pool model** (confirmed): shared DB, row-level isolation by
  `condominio_id`. `service('tenant')` (`App\Libraries\Tenant`) resolves allowed
  condominios + the active one (session key `active_condominio_id`); topbar selector
  posts to `condominio/activo` to switch. superadmin sees all; others scoped via
  `condominio_usuarios` / `personas.condominio_id`.
- **Condominios CRUD done** (first F1 module): `CondominioModel` (soft deletes, slug
  auto-gen, CFDI fields, validation), `Condominios` controller gated by
  `permission:condominios.manage`, views under `app/Views/condominios/`. Verified
  list/create end-to-end. UI uses a shared layout `app/Views/layouts/app.php`.
