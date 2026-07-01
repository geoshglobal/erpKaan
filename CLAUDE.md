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
- **Map picker:** condominio form embeds Leaflet + OSM with a draggable marker;
  `latitud`/`longitud` columns; initial center geocoded from país/estado/CP via
  Nominatim. Layout exposes `head` + `scripts` render sections for per-view assets.
- **Torres + Casas CRUD done** (scoped to active condominio via `service('tenant')`):
  `TorreModel`, `CasaModel` (+ `withTorre()` join), controllers gated by
  `permission:propiedades.manage`, `findScoped()` enforces tenant isolation on
  edit/update/delete, views under `app/Views/{torres,casas}/` with a shared
  `partials/propiedades_nav`. Verified isolation: rows of one condominio are invisible
  (and uneditable) under another.
- **is_unique on edit:** CI4 requires the `{id}` placeholder field to have its own
  validation rule (`'id' => 'permit_empty|is_natural_no_zero'`) AND the id passed in
  the update data. Pattern applied to `CondominioModel`/`Condominios::update`.
- **Topbar nav:** brand links to dashboard; role-aware main menu (Inicio, Condominios,
  Propiedades) with active-state highlight via `url_is()`.
- **Cajones CRUD done → Propiedades (F1) complete:** `CajonModel` (+ `withCasa()`),
  `Cajones` controller scoped + gated by `permission:propiedades.manage`, casa selector,
  tipo (asignado/visita/comun) and techado. `partials/propiedades_nav` now has
  Torres · Casas · Cajones. **F1 Propiedades = Torres + Casas + Cajones, all done.**
- **Personas (step 1 — base registry) done:** `PersonaModel` (scoped, soft deletes,
  contact + CFDI receptor fields, `fullName()` helper), `Personas` controller gated by
  `permission:personas.manage` with **photo upload** (validated, stored under
  `public/uploads/personas/`, served via `base_url()`; uploads git-ignored). Avatars in
  list (photo or initial). Added to main nav. Verified incl. tenant isolation + image serving.
- **File uploads:** images go to `public/uploads/<entity>/` (web-servable). The
  `public/uploads/` tree is git-ignored except `.gitkeep`. Validate with
  `is_image|mime_in|max_size`; move with `getRandomName()`.
- **Personas step 2 — owners↔casas done:** `CasaPropietarioModel` (ownersOfCasa,
  casasOfPersona, isOwner, clearPrincipal). Managed from the casa: `Casas list → Dueños`
  → `Propietarios` controller (nested routes `casas/(:num)/propietarios[...]`, gated by
  propiedades.manage, fully tenant-scoped). Add owner (persona + %, fecha, principal),
  toggle principal (single-principal invariant via clearPrincipal), remove (soft delete),
  duplicate guard. Persona edit page shows an owned-casas summary. Verified incl. isolation.
- **Personas step 3 — occupancy done:** `OcupacionModel` + `OcupanteModel`. Managed from
  the casa (`Casas list → Ocupación`): `Ocupaciones` controller with nested tenant-scoped
  routes `casas/(:num)/ocupaciones[...]` and ocupantes sub-routes. Per-casa occupancy
  history; **single-vigente invariant** (clearVigente) and marking one vigente **syncs
  `casas.tipo_ocupacion_actual`**. Ocupantes: one principal + N secundarios (single-principal
  invariant), parentesco, duplicate guard. Verified incl. vigente sync + isolation.
- **Personas roadmap:** step 1 base ✅ · step 2 owners↔casas ✅ · step 3 occupancy ✅.
  **F1 (Auth + Propiedades + Personas) complete.**

## F2 — Access control (planned)

Residents create access requests (visita w/ QR, paquetería, delivery); caseta validates
and updates status; system notifies residents on each change.

**Decisions:** resident accounts = **both** (admin creates/invites + self-register code) ·
QR validation = **camera scan + manual search** (camera OK on localhost secure context) ·
notifications = **in-app → email → push** · visits = **immediate AND scheduled window**.

**Data model:**
- `accesos` — condominio_id, casa_id, tipo(visita/paqueteria/delivery/proveedor),
  solicitante_persona_id, creado_por_user_id, nombre_visitante, empresa, telefono,
  num_personas, placas, foto_path, qr_token(unique), valido_desde, valido_hasta, estado,
  check_in_at, check_out_at, caseta_user_id, notas (+ soft deletes)
- `acceso_eventos` — acceso_id, estado_anterior, estado_nuevo, user_id, nota, created_at (audit/timeline)
- `notificaciones` — condominio_id, persona_id/user_id, acceso_id, titulo, mensaje,
  canal(in_app/email/push), leido_at, enviado_at, created_at
- `push_subscriptions` — Web Push subs (only when PWA push is built)

**Status flow:** visita/delivery/proveedor `programado → ingresado (check-in, notify "llegó")
→ finalizado (check-out, notify "salió")` + `cancelado/vencido`; paquetería
`en_caseta (notify) → entregado` + `cancelado`.

- **F2.1 visit + QR done:** `accesos` (core request/event) + `acceso_eventos` (status-change
  audit log). `AccesoModel` (estados programado/ingresado/finalizado/cancelado/vencido;
  `estadoEfectivo()` derives "vencido" at display time) + `AccesoEventoModel`. Resident
  `Visitas` controller: list/create visits scoped to their own casas (owned+occupied),
  vigencia immediate (today) or scheduled window, generates a random `qr_token`, logs the
  event; cancel. Pass view renders the QR client-side via `qrcode-generator` (CDN, no
  dependency) encoding the public `pase/{token}` URL. Public `Pase` controller shows a
  read-only pass (what the QR opens; caseta check-in lands here in F2.2). Verified: create,
  QR, public pass, cancel, cross-resident isolation.
- **Accesos supervision panel:** new permission `accesos.supervisar` (superadmin, admin,
  comité, caseta — NOT residents). `Accesos` controller lists/details all accesos of the
  active condominio (condominio-wide, NOT persona-scoped) so supervisors oversee without
  owning a property. Detail shows the `acceso_eventos` timeline. Dashboard "Accesos" card +
  nav gated by `accesos.supervisar`; residents reach their own visits via the portal.
- **Flash messages:** rendered ONCE by the layout (`layouts/app.php`) — views must NOT
  re-render `session('success')`/`session('error')` (only `session('errors')`, the
  validation array, which the layout doesn't show).
- **F2.2 caseta panel done:** `Caseta` controller (gated `caseta.operate`, tenant-scoped):
  check-in (programado/vencido → ingresado, stamps check_in_at + caseta_user_id) and
  check-out (ingresado → finalizado, stamps check_out_at), each logging an `acceso_eventos`
  row. Camera QR scanner at `caseta/escaner` (html5-qrcode CDN; works on localhost secure
  context) navigates to the scanned pass URL (validated against our `pase/` prefix to avoid
  open redirect). Check-in/out buttons appear on the public pass when a caseta operator is
  logged in (`Pase` passes `canOperate`) and on the accesos detail; `partials/caseta_actions`
  renders the right button per estado. Verified: check-in/out, timeline, anon sees no
  controls, non-caseta blocked.
- **F2.4 in-app notifications + enriched check-in done:** `notificaciones` table +
  `NotificacionModel` + `Notify::acceso()` (notifies the visit's solicitante). Topbar flat
  bell (SVG) with unread badge → `/notificaciones` (viewing marks all read). Check-in is now
  a FORM (`Caseta::checkinForm` GET → `checkin` POST) capturing pax_ingresaron (JS alert +
  call/WhatsApp buttons to the resident when it exceeds the registered count),
  ingreso_vehiculo + folio_corbatin + placas, and an ID photo (uploads/accesos) or a "sin ID"
  flag + reason. `Phone` library builds wa.me/tel links defaulting to +52. Check-in fires
  "Tu visita llegó", check-out "Tu visita salió". Topbar: user email is a `<details>`
  dropdown containing Cerrar sesión. Email/push channels still pending. **F2 core = F2.0–F2.2
  + F2.4 done.** Remaining: F2.3 paquetería/delivery, F2.4 email/push, F2.5 guest access.

### F2 backlog — visitor vehicle access + parking (DONE 2026-07-01)

`accesos` gained `permite_vehiculo`, `cajon_id`, `autorizacion_cajon`.
1. ✅ Resident authorizes vehicle on the visit (`permite_vehiculo` checkbox on the new-visit
   form; shown on detail; caseta check-in warns if not authorized).
2. ✅ Visitor parking via `cajones` tipo='visita'. `Parking` library computes available spots
   (visitor cajones not held by an `ingresado` acceso). Check-in with a vehicle assigns a free
   visitor spot; if none free, caseta can request the resident's spot → `autorizacion_cajon`
   'pendiente' + notification → resident approves/denies at `portal/autorizaciones` (approve
   assigns a casa cajon, sets 'autorizado'). Spot frees automatically on check-out (estado
   leaves 'ingresado').
3. ✅ Caseta "Tomar foto" button: live camera (getUserMedia) → canvas → sets the id_foto file
   input via DataTransfer; falls back to file picker. All verified end-to-end.

Original request (2026-06-30):
1. **Resident authorizes vehicle on the visit.** The "Nueva visita" form (resident) should
   include "¿se permite acceso en vehículo?" — add `accesos.permite_vehiculo` (bool). This
   is the resident's authorization at creation, distinct from caseta's `ingreso_vehiculo`
   (what actually happened at the gate).
2. **Visitor parking (cajones de visita).** Condo config has 0+ visitor spots (model via
   existing `cajones` with `tipo='visita'`, or a count on the condominio). At check-in with a
   vehicle, caseta must check if a visitor spot is **available** (not occupied by another
   currently-inside vehicle access). If none free → fall back to a **resident's spot**, which
   **the resident must authorize** (notification + approve/deny flow before/at entry). Needs:
   occupancy tracking of visitor spots, and an authorization request to the resident.
3. **Caseta "take photo" button for the ID.** The check-in `id_foto` input uses `capture`
   (opens the camera on mobile only). Add an explicit "Tomar foto" button that opens the live
   camera via `getUserMedia`, captures a frame to a canvas, and submits it as the ID photo —
   so the operator captures directly on desktop/tablet without browsing for a file (falls back
   to the file picker where the camera isn't available; needs localhost/HTTPS).

### F2 planned — Events (guest list + event QR) (NOT built)

Requested 2026-07-01. A resident hosts an event and manages a **guest list**; instead of one
QR per guest, the event has a **single shared event QR**.
- **Pax limit:** set as a condominio-config default AND/OR per-event by admin at creation
  (per-event overrides the condo default).
- **Data (proposed):** `eventos` (condominio_id, casa_id, anfitrion_persona_id, nombre,
  fecha/rango, `qr_token` unique, `pax_limite`, `pax_ingresados` counter, estado) +
  `evento_invitados` (evento_id, nombre, `pax` registered, `pax_ingresados`).
- **Flow:** resident submits the guest list (each guest has a registered pax count) and shares
  the one event QR. At caseta, scanning the event QR shows the event + guest list; caseta
  registers arrivals, incrementing `pax_ingresados` (per guest and total). When total reaches
  `pax_limite`, **notify the resident** that the limit was reached. Reuses the accesos +
  notifications infrastructure; complements individual visits (this is a group/event access).

### F2.4 email channel — config note

`.env` already carries `mail.*` (outgoing `kaan.geoshglobal.com:465`, protocol smtp/SSL,
`mail.username`/`mail.password`). When building the email notification channel, map these to
CI4 `Config\Email` (SMTP, SSL on 465) and mirror in-app notifications to email.

**Sub-steps:** F2.0 resident accounts (prereq — residents need Shield logins linked to
personas) → F2.1 accesos model + resident visit + QR → F2.2 caseta panel (scan/validate,
check-in/out, status) → F2.3 paquetería + delivery → F2.4 notifications (in-app→email→push)
→ F2.5 guest temporary access (ties to F5). Push/HTTPS note: camera + Web Push need a secure
context (localhost OK; prod needs HTTPS).

- **Occupant invitations + portal self-service (F2.0b) done:** `invitaciones` extended
  (tipo cuenta|ocupante, ocupacion_id, rol_ocupante, nullable persona_id). `OccupantInvite`
  service handles new-user vs existing-user (password-verified) linking + add/move choice
  (cross-condominio: one login → many personas). Admin invites occupants from an ocupación;
  residents (principal) manage their own casa's occupants (name-only add = persona without
  login; invite-for-account forced to inquilino role). Portal self-edit of own persona
  (generales + fiscales). Occupant limits: `condominios.max_ocupantes` default +
  `casas.max_ocupantes` override (NULL=unlimited), enforced via `OccupancyRules` at all add
  points (counts occupants + pending invites). `OcupanteModel::ofOcupacion` returns
  `user_id` so account status renders correctly. Persona edit shows "Casas que ocupa".
  Security: tokens single-use/14-day; identity-claim requires the account password;
  resident-issued invites can't escalate role; all actions tenant/owner-scoped.
- **F2.0 resident accounts done:** Shield open registration disabled
  (`Auth::$allowRegistration=false`) — invite-only. `ResidentAccount` library creates a
  Shield user (email login), activates, adds the role group (dueno/inquilino/huesped) and
  links `personas.user_id`. Managed from a persona (`Personas list → Acceso`): `Cuentas`
  controller (admin creates account directly OR generates a per-persona invitation, 14-day
  token in `invitaciones`). Public token-gated self-register: `Registro` controller +
  `registro/(:segment)` routes → auto-login → `/portal`. `Portal` controller = resident
  landing (owned casas + current ocupaciones). "Mi portal" in nav for residents. Verified
  end-to-end (admin-create, invite+self-register, resident login, permission isolation).
