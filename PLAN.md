# erpKaan — Plan / Roadmap

> Documento de handoff para retomar el proyecto desde un chat nuevo con poco contexto.
> El detalle técnico e histórico completo está en [`CLAUDE.md`](CLAUDE.md); este archivo es el
> **mapa de qué está hecho y qué falta**. Actualiza este archivo al cerrar cada módulo.

## Qué es
Plataforma de **administración de condominios (HOA)** para México. CodeIgniter 4 (PHP), web +
API REST, **multi-tenant** (varios condominios). Auth con CodeIgniter Shield. App en vivo:
`https://kaan.geoshglobal.com`.

## Restricciones clave (LEER antes de tocar)
- **Multi-tenant Pool**: BD compartida, aislamiento por `condominio_id`. `service('tenant')` resuelve
  condominio activo. Todo query de dominio va scopeado por condominio.
- **BD de prod = `SELECT/INSERT/UPDATE`** (sin DDL, sin DELETE) — **excepto DELETE en tablas
  `auth_*` de Shield**. Dominio: **soft deletes** siempre; **front-load** columnas futuras.
- **Local y prod comparten la MISMA BD**. `spark migrate` desde local altera el esquema de prod
  → ser deliberado. Las migraciones ya aplicadas NO se re-corren en el server.
- **Zona horaria**: se guarda en UTC, se muestra con `App\Libraries\Tz` + helper `dt()`.
- **Marca**: usar variables CSS (`--accent` #2C6E52, `--accent2` #43A074, `--sand`, `--tint`,
  `--cream`, `--ink`) y `.mono`; assets en `public/brand/`. No hardcodear hex.
- **Deploy**: push a `main` → GitHub Actions FTPS a prod. `.env` nunca se commitea/despliega.
  Convención de ramas: `fase/fX.Y-*` → merge a `main`.
- **Login superadmin**: `admin@erpkaan.mx` / `Kaan!2026Admin`.

## Fases
| Fase | Alcance | Estado |
|---|---|---|
| F1 Fundamentos | Auth+roles, Propiedades (condominios→torres→casas→cajones), Personas (dueños/inquilinos/ocupación) | ✅ Hecho |
| F2 Control de acceso | Cuentas residente, visitas+QR, caseta, paquetería/delivery/proveedor, notificaciones (in-app/email/push) | 🟡 Núcleo hecho; faltan módulos abajo |
| F3 Finanzas | Cuotas, morosidad, multas, estados de cuenta, pagos online, CFDI | ⬜ Pendiente |
| F4 Amenidades + comms | Reservas de áreas comunes, anuncios, tickets, encuestas | ⬜ Pendiente |
| F5 Renta vacacional | Huéspedes, requisitos, check-in/out, channel managers | ⬜ Pendiente |

## Hecho ✅
- **F1**: Shield (roles/permisos), CRUD condominios/torres/casas/cajones, personas + dueños↔casas +
  ocupación (vigente/principal), mapa (Leaflet), fotos.
- **F2.0**: cuentas de residente (invite-only) + invitaciones + auto-registro; ocupantes/invitaciones.
- **F2.1**: visitas + QR (pase público), cancelar.
- **F2.2**: panel caseta (escáner QR + check-in/out enriquecido: pax, vehículo, folio, ID/foto).
- **F2.3**: paquetería (en_caseta→entregado con **foto de entrega**), delivery/proveedor (registro
  directo en caseta y **aviso del residente**), estados `en_caseta`/`entregado`, portal/paquetes.
- **F2.4**: notificaciones **in-app + email (SMTP) + push (PWA/VAPID)** + **preferencias por usuario**;
  banner para activar push.
- **Parking**: cajones de visita, autorización de cajón del residente (pre-auth en creación, o
  solicitar/forzar en caseta); vehículo/cajón solo aplica a proveedor (no delivery).
- **Horarios de acceso por condominio y POR DÍA** (delivery/proveedor) con alertas/restricciones.
- **Zona horaria** por condominio + por usuario (`Tz`, `dt()`), arregla desfase GMT-0.
- **Reasignar casa** de un registro + corrección de notificación al residente equivocado.
- **Paginación + filtros** (tipo/casa/texto/estado + **fecha, default últimos 15 días**) en
  accesos/visitas/paquetes/notificaciones.
- **Rebrand** completo (manual de marca): paleta, Plus Jakarta Sans + Space Mono, logos oficiales,
  favicon/manifest PWA, login (Shield) tematizado, correos.

## Pendiente / Planeado ⬜ (orden sugerido)
Cada uno con spec breve; el detalle está en la sección "F2 planned …" de CLAUDE.md.

1. **Personal de servicio por vivienda** — alta de personal recurrente por casa (empleada, jardinero…)
   con **horario de labor**; caseta le da acceso identificando con **ID (sin QR ni teléfono)**.
   Reusa `Horario` (ventanas por día) + check-in/out. Tabla `personal_servicio`.
2. **Directorio de residentes + credenciales por propiedad (acceso peatonal en caseta)**:
   - Caseta **consulta habitantes por casa con FOTOS** (dueños + ocupantes) para acceso peatonal sin QR.
   - **Vehículos + número de TAG** por propiedad (tabla `vehiculos` ya existe; front-load `tag`).
   - **Llaveros** de acceso peatonal por propiedad (nueva tabla `llaveros`).
   - Caseta = consulta/verificación; alta desde propiedades o portal del residente.
3. **Eventos (lista de invitados + UN QR de evento)** — límite de pax por condo y/o por evento;
   caseta registra llegadas incrementando pax; avisa al residente al llegar al límite.
   Tablas `eventos` + `evento_invitados`.
4. **Bloqueos por morosidad (configurable por condominio)** — cruza F2/F3. Cuando una casa está
   morosa: (a) **bloquear solicitudes** (visitas/deliveries/amenidades, total o por tipo),
   (b) **alertar a caseta** al llegar un acceso de casa morosa, (c) **restricciones** graduales.
   Config por condominio (`morosidad_config` JSON). Depende del estado de cuenta de F3.
5. **F2.5 Huésped temporal** — registro con vigencia, requisitos, check-in/out. Base para F5.
6. **Dev (menor)**: `TypeError` del debug Toolbar de CI4 en desarrollo (json_encode de datos con
   bytes no-UTF8; solo dev, prod no lo tiene). Opciones: desactivar toolbar en dev, o sanear la fila.

## Cómo trabajar
- Rama `fase/fX.Y-*`; commits por sub-paso; verificación end-to-end (curl o smoke).
- Migraciones dev-authored; correr `php spark migrate` (recordar: toca la BD compartida).
- Al terminar un módulo: actualizar **este PLAN.md** (marcar hecho) + el "Project log" de CLAUDE.md.
- Merge a `main` + push = deploy. Confirmar con el usuario antes de desplegar cambios de prod.
