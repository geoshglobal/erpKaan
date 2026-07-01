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
| **production**| **`SELECT`, `INSERT`, `UPDATE`** on all tables + **`DELETE` on `auth_*` only** |

Production has **no DDL** (`CREATE`/`ALTER`/`DROP`) and **no `DELETE` on domain tables**.

> **Shield exception (granted 2026-07-01):** the prod user (`cycoasis_kaanAdmin`) has
> `DELETE` on the Shield housekeeping tables — `auth_remember_tokens`, `auth_identities`,
> `auth_groups_users`, `auth_permissions_users` — because the framework hard-deletes ephemeral
> security rows (logout purges remember tokens; role/permission changes remove rows). This does
> NOT relax the domain rule: **domain tables still never get DELETE — soft deletes only.** The
> logout `DELETE` error (`auth_remember_tokens`) was the trigger; Shield's `Session::logout()`
> always calls `purgeRememberTokens()`, even with remember-me off.

Implications for all code and schema work:

- **No hard deletes on domain tables in prod.** Use **soft deletes** (CI4 Model `$useSoftDeletes = true`, `deleted_at` column). A soft delete is an `UPDATE`, so it works under prod privileges. Never call raw `DELETE`/`->delete()` paths on domain tables in prod (Shield's own `auth_*` deletes are fine — see the exception above).
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

## Branding (erpKaan — manual de marca)

Brand kit lives in `public/brand/` (svg/png logos, favicon, `README.txt` with the palette; the
`*.html` manual is git-ignored, not deployed). Palette: **Verde profundo `#2C6E52`** (primary),
**Verde medio `#43A074`** (highlights/active), **Acento arena `#F1D492`**, **Tinta `#1C2621`**
(topbar/ink), **Verde tinte `#E4F0E9`**, **Crema `#F6F4ED`** (page bg). Fonts: **Plus Jakarta Sans**
(600/700/800, UI) + **Space Mono** (`.mono`, codes) via Google Fonts.

Applied in `layouts/app.php` (CSS vars `--accent/--accent2/--sand/--tint/--cream/--ink`, reversed
logo in the topbar, favicon + apple-touch-icon + `manifest.webmanifest`, `theme-color`). Auth pages
(Shield) themed via `Config\Auth::$views['layout'] = 'auth/layout'` → `app/Views/auth/layout.php`
(Bootstrap kept for the forms, brand overrides + horizontal logo). Emails (`Mailer::layout`) and the
push icons (`public/sw.js`, `manifest.webmanifest`) use the brand green + isotipo PNGs. When adding
views, use the CSS vars / `.mono` — avoid hardcoded hex.

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
- **F2.4 email channel done (code):** `App\Libraries\Mailer` (SMTP from `mail.*` env; port
  465 → SSL crypto; `send()`, `layout()` HTML shell) gated by `notify.email` env flag (off in
  dev so tests never hit SMTP). `Notify::acceso` now mirrors each in-app notification to the
  solicitante's `personas.email` (best-effort; stamps `notificaciones.email_enviado_at` /
  `email_error`). Migration `AddEmailTrackingToNotificaciones` (front-loaded tracking cols).
  `php spark mail:test <correo>` smoke-tests SMTP. `.env`: added `mail.fromEmail`/`mail.fromName`
  + `notify.email=false`. Migration applied to the shared DB; `mail:test` verified end-to-end
  (SMTP OK). Flip `notify.email=true` on prod to enable. **F2.4 email done.**
- **F2.4 push (PWA) done (code):** `minishlink/web-push` (VAPID). Keys in `.env`
  (`push.publicKey/privateKey/subject`, `notify.push=false` gate). `push_subscriptions` table
  (soft deletes; `endpoint_hash` sha256 unique) + `PushSubscriptionModel` (upsert/revive by
  hash, `removeByEndpoint` on 410). `App\Libraries\Push::toUser()` sends to all of a user's
  subs, drops expired ones. `PushController` (subscribe/unsubscribe JSON endpoints, session).
  Client: `public/sw.js` (push + notificationclick → focus/navigate), `public/js/push.js`
  (SW register + subscribe toggle, `#push-toggle`). `Notify::acceso` now fires in-app + email
  + push. **Needs HTTPS on prod (localhost OK) + `notify.push=true`.**
- **Per-user notification prefs done:** `Config\Notificaciones` (defaults email+push on) +
  `App\Libraries\NotifPrefs` (per-user via CodeIgniter Settings context `user:{id}`). A channel
  fires only when global env flag ON **and** user hasn't opted out. Settings page
  `notificaciones/preferencias` (email/push checkboxes + browser push permission toggle),
  linked from the user dropdown. Global **push-permission banner** in the layout (shows when
  `Notification.permission !== 'granted'`; explains blocked state; session-dismissible).
  Verified: prefs round-trip (save→persist), banner render, sw.js/push.js served. **F2.4 done
  (email + push + prefs).** Remaining F2: F2.3 paquetería/delivery, F2.5 guest access.
- **F2.3 paquetería + delivery done:** caseta "Registro directo" (`caseta/registro`) for
  arrivals no resident pre-registered — `tipo` paquetería|delivery|proveedor. Paquetería →
  estado `en_caseta` (stays at the gate) → `Caseta::entregar` marks `entregado`; delivery/
  proveedor → `ingresado` now (walk-in) → existing checkout → `finalizado`. New estados
  `en_caseta`/`entregado` + `AccesoModel::TIPOS` const. Recipient resolved via
  `App\Libraries\CasaResidents` (owners + vigente occupants, principal first; caseta picks from
  a per-casa JSON map, else default). Notifies the resident (in-app+email+push). Resident sees
  them at `portal/paquetes` (`Visitas::paquetes`, tipo-filtered); `Visitas::index` now
  visita-only. `caseta_actions` partial + accesos list/detail updated for the new estados.
- **Parking pre-authorization (visit creation):** `accesos.autoriza_cajon_propio` (bool). On the
  resident's new-visit form, checking "permitir vehículo" reveals "autorizo el uso de mi cajón
  si no hay lugar de visitas". At caseta check-in, when no visitor spot is free AND the resident
  pre-authorized, their own cajon is assigned automatically (`autorizacion_cajon='autorizado'`)
  with no gate request — the solicitar/forzar flow only shows when NOT pre-authorized.
  `Caseta::residentCajonId()` helper (forzarCajon refactored onto it). Check-in auto-opens the
  vehicle section when `permite_vehiculo`.
- **Notification URL fix:** callers now pass RELATIVE paths to `Notify::acceso`; `Notify::absUrl()`
  (idempotent: absolute→as-is, relative→site_url) is applied once at each sink (in-app view,
  email, push). Fixes the doubled `.../https:/.../` link seen in prod.
- **Resident announces delivery/proveedor (F2.3+):** residents pre-announce an expected
  `delivery` or `proveedor` from `portal/avisos/nuevo` (`Visitas::avisar`/`crearAviso`, estado
  `programado`) so caseta grants access smoothly; **caseta is notified** via `Notify::caseta()`
  (in-app+push to condominio users in the Shield `caseta` group, resolved through
  `condominio_usuarios`). The vehicle/parking sub-section (permite_vehiculo + autoriza_cajon_propio)
  shows **only for proveedor**, never delivery. Appears in `portal/paquetes` and the caseta panel.
- **Configurable access schedules per condominio (per-day):** `condominios.horarios` (JSON) holds a
  SEPARATE window per weekday for delivery and proveedor: `{"delivery":{"activo":true,
  "dias":{"1":{"desde":"09:00","hasta":"18:00"}, ...}}}`. `App\Libraries\Horario` (forTipo/check/
  resumen) evaluates in the condominio's timezone. Admin sets it in the condominio form (per-day
  rows). Residents see the allowed window when announcing and are **blocked** if the arrival time is
  outside it (restriction); caseta sees an **alert** (not blocked) at check-in and registro.
- **Timezone (fixes GMT-0):** datetimes stored UTC, displayed in a resolved zone —
  user preference → active condominio (`condominios.timezone`) → app default `America/Mexico_City`.
  `App\Libraries\Tz` + global `dt()` helper (autoloaded via BaseController `$helpers=['kaan']`),
  applied across the access views. Condominio form sets its zone; users override in
  `notificaciones/preferencias`. `Visitas::vigencia/parseDateTime` now tz-aware (datetime-local
  inputs interpreted in the user's zone → UTC; "hoy" = end of day in the user's zone).
- **Reusable camera capture:** `partials/camera_capture` + `public/js/camera.js` add a live
  "📷 Tomar foto" button (getUserMedia → canvas → sets the file input, independent of the file
  picker) to any caseta photo field — used in `caseta/registro` and the new deliver-photo form.
- **Mark-delivered captures a photo:** `Caseta::entregarForm` (GET) → `caseta/entregar` view with
  file+camera → `entregar` (POST) stores `accesos.foto_entrega_path` as proof, then notifies.
- **Pagination + accesos search/filters:** transactional listings paginate (CI4 `paginate()` +
  a custom `partials/pager` template registered as `kaan` in `Config\Pager`): accesos (20/pg),
  visitas + paquetes (15/pg), notificaciones (20/pg). The **accesos supervision panel** (used by
  superadmin/caseta) gained **tipo tabs** (Todos/Visita/Delivery/Proveedor/Paquetería) and a
  **filter form**: by **departamento (casa)** + free-text (visitor/casa/empresa) + estado — so
  caseta can find a pass by department when NOT scanning the QR. `AccesoModel::scopeForCondominio`
  / `scopeForSolicitante` build filtered queries; `$pager->only([...])` preserves filters across
  pages. **Date-range filter (default last 15 days)** on accesos, visitas, paquetes and
  notificaciones via `BaseController::dateRange()` (tz-aware local→UTC boundaries on `created_at`,
  `Tz::boundary`/`Tz::localDate`) + reusable `partials/date_filter`.
- **Reassign casa + notification correction:** caseta can correct a mis-assigned caseta-registered
  acceso (paquetería/delivery/proveedor still en_caseta/programado/ingresado) via `caseta/accesos/
  {id}/reasignar` ("✏️ Corregir casa"). It updates `casa_id`+`solicitante_persona_id`, logs an event,
  **notifies the previous resident of the mistake** (`Notify::toPersona`) and **neutralizes their old
  in-app notification** (`NotificacionModel::markCorrected` — an UPDATE, prod-safe), then sends the
  normal arrival notification to the new resident. `Notify::acceso` now delegates to `toPersona`.
- **Delivery/proveedor notifications are tipo-specific:** caseta check-in/out and cajón messages
  now say "Tu delivery/proveedor llegó/salió" (not "visita") and link non-visita accesos to
  `portal/paquetes` instead of `portal/visitas/{id}`.
- **Mobile-first layout:** `layouts/app.php` rebuilt mobile-first — sticky topbar collapses to a
  CSS-only hamburger (`#navtoggle` checkbox → `.mainnav`), `.bar-right` cluster (tenant selector,
  bell, user menu with email→👤 glyph on phones), inputs at 16px (no iOS zoom), 42px tap targets,
  `.grid2` stacks, wide `table.grid` scrolls horizontally on phones, desktop enhancements behind
  `@media (min-width:768px)`. New shared components: `.segmented`, `.head-actions`, `.cards-list`.
  Verified: all pages 200, hamburger/nav/bar-right present, registro form + paquetes render.

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

### F2 planned — Personal de servicio por vivienda (NOT built)

Requested 2026-07-01. A resident registers recurring **household service staff** (empleada,
jardinero, niñera, etc.) tied to their casa, with a **work schedule** so caseta can grant access
routinely. **No QR and no phone required** — many staff don't have a phone; caseta identifies them
by **presenting an ID** (name + photo on file). Proposed data: `personal_servicio` (condominio_id,
casa_id, persona/nombre, foto_path, tipo/rol, activo, notas) + a per-weekday schedule (reuse the
`Horario` per-day windows shape). Caseta panel: search staff by casa/name, verify the photo/ID,
register entry/exit (reuse the accesos check-in/out + `acceso_eventos`), with an out-of-schedule
alert like delivery/proveedor. Resident manages their casa's staff from the portal. Complements
individual visits and the delivery/proveedor announce flow.

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
