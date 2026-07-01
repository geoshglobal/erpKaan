<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'erpKaan') ?></title>
    <meta name="theme-color" content="#1C2621">
    <link rel="icon" href="<?= base_url('brand/favicon.ico') ?>" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('brand/png/erpKaan-isotipo-32.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('brand/png/erpKaan-isotipo-256.png') ?>">
    <link rel="manifest" href="<?= base_url('manifest.webmanifest') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Space+Mono&display=swap" rel="stylesheet">
    <style>
        /* ---- Brand palette (erpKaan manual de marca) ---- */
        :root {
            --bg:#1C2621;        /* Tinta — topbar/dark surfaces */
            --panel:#fff;
            --muted:#6b7a70;
            --accent:#2C6E52;    /* Verde profundo — primary actions */
            --accent2:#43A074;   /* Verde medio — highlights/active */
            --sand:#F1D492;      /* Acento arena */
            --tint:#E4F0E9;      /* Verde tinte */
            --cream:#F6F4ED;     /* Crema — page background */
            --ink:#1C2621;
            --line:#dbe5de;
        }
        * { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; }
        body { margin:0; font-family: 'Plus Jakarta Sans', system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background:var(--cream); color:var(--ink); }
        h1, h2, h3, .brand { font-weight:800; letter-spacing:-.01em; }
        .mono { font-family: 'Space Mono', ui-monospace, SFMono-Regular, Menlo, monospace; }

        /* Topbar: collapses to a hamburger on small screens */
        header.topbar { background:var(--bg); color:#fff; display:flex; flex-wrap:wrap; align-items:center; gap:.6rem; padding:.5rem 1rem; position:sticky; top:0; z-index:40; }
        header.topbar .brand { order:1; margin-right:auto; display:inline-flex; align-items:center; text-decoration:none; }
        header.topbar .brand img { height:32px; width:auto; display:block; }
        .nav-toggle { display:none; }
        .hamburger { order:0; display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; margin-left:-.35rem; color:#cbd5e1; cursor:pointer; border-radius:8px; font-size:1.4rem; user-select:none; }
        .hamburger:hover { color:#fff; background:#2a3a31; }
        nav.mainnav { order:3; flex-basis:100%; display:none; flex-direction:column; gap:.15rem; padding:.25rem 0 .4rem; }
        .nav-toggle:checked ~ nav.mainnav { display:flex; }
        nav.mainnav a { color:#cbd5e1; text-decoration:none; font-size:.98rem; padding:.7rem .6rem; border-radius:8px; line-height:1.1; }
        nav.mainnav a:hover { color:#fff; background:#2a3a31; }
        nav.mainnav a.active { color:#fff; background:var(--accent); font-weight:600; }
        .bar-right { order:2; display:flex; align-items:center; gap:.9rem; }
        .bar-right a { color:#cbd5e1; text-decoration:none; }
        .bar-right a:hover { color:#fff; }
        .bell { position:relative; display:inline-flex; align-items:center; color:#cbd5e1; padding:.2rem; }
        .bell:hover { color:#fff; }
        .bell .badge { position:absolute; top:-6px; right:-8px; background:#ef4444; color:#fff; border-radius:999px; font-size:.62rem; font-weight:700; padding:.05rem .3rem; }
        .user-menu { position:relative; }
        .user-menu summary { list-style:none; cursor:pointer; color:#cbd5e1; display:inline-flex; align-items:center; gap:.35rem; font-size:.9rem; }
        .user-menu summary::-webkit-details-marker { display:none; }
        .user-menu summary:hover, .user-menu[open] summary { color:#fff; }
        .user-menu .user-glyph { font-size:1.15rem; }
        .user-menu .email-text { display:none; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .user-menu .menu { position:absolute; right:0; top:calc(100% + .4rem); background:#fff; color:#1C2621; border:1px solid var(--line); border-radius:8px; min-width:210px; box-shadow:0 8px 24px rgba(0,0,0,.18); z-index:30; overflow:hidden; }
        .user-menu .menu a { display:block; padding:.7rem .9rem; color:#1C2621; font-size:.9rem; }
        .user-menu .menu a:hover { background:#eaf1ec; }
        .tenant-select { display:flex; align-items:center; gap:.4rem; }
        .tenant-select label { display:none; color:#94a3b8; font-size:.8rem; }
        .tenant-select select { background:#2a3a31; color:#fff; border:1px solid #3a4a41; border-radius:8px; padding:.35rem .5rem; font-size:.85rem; max-width:44vw; }

        main { max-width:1040px; margin:1rem auto; padding:0 1rem; }
        .alert { padding:.75rem 1rem; border-radius:8px; margin-bottom:1rem; font-size:.9rem; }
        .alert.error { background:#fee2e2; color:#991b1b; }
        .alert.success { background:#dcfce7; color:#166534; }
        .badge { display:inline-block; background:var(--tint); color:var(--accent); border-radius:999px; padding:.15rem .6rem; font-size:.75rem; font-weight:600; }

        .btn { background:var(--accent); color:#fff; border:none; padding:.55rem 1rem; border-radius:8px; text-decoration:none; font-size:.9rem; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:.35rem; min-height:42px; }
        .btn:hover { filter:brightness(.95); }
        .btn.secondary { background:var(--tint); color:var(--accent); }
        .btn.danger { background:#ef4444; color:#fff; }
        .btn.small { padding:.35rem .7rem; font-size:.82rem; min-height:34px; }

        .page-head { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:.6rem; margin-bottom:1.1rem; }
        .page-head h1 { margin:0; font-size:1.25rem; }
        .head-actions { display:flex; gap:.5rem; flex-wrap:wrap; }

        .table-wrap { overflow-x:auto; -webkit-overflow-scrolling:touch; border-radius:12px; }
        table.grid { width:100%; border-collapse:collapse; background:#fff; border:1px solid var(--line); border-radius:12px; overflow:hidden; }
        table.grid th, table.grid td { text-align:left; padding:.7rem .9rem; border-bottom:1px solid var(--line); font-size:.88rem; white-space:nowrap; }
        table.grid th { background:#f8fafc; color:#475569; font-weight:600; }
        table.grid tr:last-child td { border-bottom:none; }

        .muted { color:var(--muted); }
        .pill { display:inline-block; border-radius:999px; padding:.1rem .55rem; font-size:.72rem; font-weight:600; }
        .pill.on { background:var(--tint); color:var(--accent); }
        .pill.off { background:#f0ece0; color:#8a7a4e; }

        .cards-list { display:flex; flex-direction:column; gap:.75rem; }
        .list-card { padding:.9rem 1rem; }
        .pager { display:flex; flex-wrap:wrap; gap:.3rem; justify-content:center; margin-top:1rem; }
        .pager .pg { min-width:38px; height:38px; display:inline-flex; align-items:center; justify-content:center; padding:0 .6rem; border:1px solid var(--line); border-radius:8px; background:#fff; color:#1C2621; text-decoration:none; font-size:.9rem; }
        .pager .pg:hover { background:#eaf1ec; }
        .pager .pg.active { background:var(--accent); color:#fff; border-color:var(--accent); font-weight:600; }
        .filters { display:flex; flex-wrap:wrap; gap:.5rem; align-items:end; margin-bottom:1rem; }
        .filters .field { margin:0; }
        .tabs { display:flex; flex-wrap:wrap; gap:.35rem; margin-bottom:1rem; }
        .tabs a { padding:.45rem .8rem; border-radius:999px; border:1px solid var(--line); background:#fff; color:#3a4a41; text-decoration:none; font-size:.85rem; }
        .tabs a.active { background:var(--accent); color:#fff; border-color:var(--accent); font-weight:600; }

        .card { background:#fff; border:1px solid var(--line); border-radius:12px; padding:1.1rem; }
        form.card { background:#fff; border:1px solid var(--line); border-radius:12px; padding:1.1rem; }
        .field { margin-bottom:1rem; }
        .field label { display:block; font-size:.82rem; font-weight:600; margin-bottom:.3rem; color:#3a4a41; }
        .field input[type=text], .field input[type=email], .field input[type=number], .field input[type=password], .field input[type=date], .field input[type=datetime-local], .field input[type=time], .field input[type=tel], .field select, .field textarea {
            width:100%; padding:.6rem .7rem; border:1px solid #cbd5e1; border-radius:8px; font-size:16px; }
        input[type=text], input[type=email], input[type=number], input[type=password], input[type=tel], input[type=date], input[type=datetime-local], input[type=time], select, textarea { font-size:16px; }
        .segmented { display:flex; flex-wrap:wrap; gap:.4rem; }
        .segmented label { border:1px solid var(--line); border-radius:999px; padding:.5rem .85rem; cursor:pointer; font-size:.9rem; display:inline-flex; gap:.35rem; align-items:center; background:#fff; }
        .segmented input { position:absolute; opacity:0; width:0; height:0; }
        .segmented label:has(input:checked) { background:var(--accent); color:#fff; border-color:var(--accent); }
        .stepper { display:inline-flex; align-items:stretch; border:1px solid #cbd5e1; border-radius:8px; overflow:hidden; background:#fff; }
        .stepper button { border:none; background:#eaf1ec; color:#1C2621; width:46px; font-size:1.3rem; line-height:1; cursor:pointer; }
        .stepper button:hover { background:#e2e8f0; }
        .stepper input { border:none; width:70px; text-align:center; font-size:1rem; padding:.55rem 0; -moz-appearance:textfield; appearance:textfield; }
        .stepper input::-webkit-outer-spin-button, .stepper input::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
        .grid2 { display:grid; grid-template-columns:1fr; gap:1rem; }
        .form-actions { display:flex; gap:.6rem; margin-top:1rem; flex-wrap:wrap; }
        fieldset { border:1px solid var(--line); border-radius:10px; padding:1rem; margin:0 0 1.25rem; min-width:0; }
        legend { font-size:.8rem; font-weight:700; color:#2C6E52; padding:0 .4rem; }

        .notif-banner { display:none; align-items:center; flex-wrap:wrap; gap:.6rem .75rem; background:#fffbeb; border:1px solid #fde68a; color:#92400e; border-radius:10px; padding:.7rem 1rem; margin-bottom:1rem; font-size:.88rem; }
        .notif-banner .nb-actions { margin-left:auto; display:flex; gap:.5rem; align-items:center; white-space:nowrap; }
        .notif-banner a.nb-cta { background:#2C6E52; color:#fff; text-decoration:none; padding:.4rem .8rem; border-radius:8px; font-weight:600; }
        .notif-banner button.nb-close { background:none; border:none; color:#92400e; cursor:pointer; font-size:1.1rem; line-height:1; }

        /* On phones, wide tables scroll horizontally instead of overflowing the page. */
        @media (max-width:767px) {
            table.grid { display:block; overflow-x:auto; -webkit-overflow-scrolling:touch; }
        }

        /* ---- Desktop enhancements ---- */
        @media (min-width:768px) {
            header.topbar { flex-wrap:nowrap; gap:1.25rem; padding:0 1.25rem; min-height:56px; }
            header.topbar .brand { margin-right:.5rem; order:1; }
            .hamburger { display:none; }
            nav.mainnav { order:2; flex-basis:auto; display:flex; flex-direction:row; align-items:center; gap:.25rem; padding:0; margin-right:auto; }
            nav.mainnav a { font-size:.9rem; padding:.4rem .7rem; }
            .bar-right { order:3; }
            .user-menu .user-glyph { display:none; }
            .user-menu .email-text { display:inline-block; }
            .tenant-select label { display:inline; }
            .tenant-select select { max-width:none; }
            main { margin:1.5rem auto; padding:0 1.25rem; }
            .page-head h1 { font-size:1.4rem; }
            table.grid th, table.grid td { white-space:normal; }
            .grid2 { grid-template-columns:1fr 1fr; }
            .field input[type=text], .field input[type=email], .field input[type=number], .field input[type=password], .field input[type=date], .field input[type=datetime-local], .field input[type=tel], .field select, .field textarea { font-size:.92rem; }
        }
    </style>
    <?= $this->renderSection('head') ?>
</head>
<body>
    <?php $isAuthed = function_exists('auth') && auth()->loggedIn(); ?>
    <header class="topbar">
        <?php if ($isAuthed): ?>
            <input type="checkbox" id="navtoggle" class="nav-toggle">
            <label for="navtoggle" class="hamburger" aria-label="Menú" title="Menú">☰</label>
        <?php endif; ?>
        <a class="brand" href="<?= site_url($isAuthed ? 'dashboard' : '/') ?>">
            <img src="<?= base_url('brand/png/erpKaan-logotipo-reversed-1020.png') ?>" alt="erpKaan">
        </a>
        <?php if ($isAuthed): ?>
            <?php
            $u    = auth()->user();
            $menu = [
                ['label' => 'Inicio',      'url' => site_url('dashboard'),   'active' => url_is('dashboard'),                       'show' => true],
                ['label' => 'Condominios', 'url' => site_url('condominios'), 'active' => url_is('condominios*'),                    'show' => $u->can('condominios.manage')],
                ['label' => 'Propiedades', 'url' => site_url('casas'),       'active' => url_is('casas*') || url_is('torres*') || url_is('cajones*'), 'show' => $u->can('propiedades.manage')],
                ['label' => 'Personas',    'url' => site_url('personas'),    'active' => url_is('personas*'),                       'show' => $u->can('personas.manage')],
                ['label' => 'Accesos',     'url' => site_url('accesos'),     'active' => url_is('accesos*'),                        'show' => $u->can('accesos.supervisar')],
                ['label' => 'Mi portal',   'url' => site_url('portal'),      'active' => url_is('portal*'),                         'show' => $u->can('self.access') && ! $u->can('personas.manage')],
            ];
            $tenant      = service('tenant');
            $allowed     = $tenant->allowedCondominios();
            $activeCondo = $tenant->activeId();
            $unread      = (new \App\Models\NotificacionModel())->unreadCount((int) auth()->id());
            ?>
            <div class="bar-right">
                <?php if ($allowed !== []): ?>
                    <form class="tenant-select" method="post" action="<?= site_url('condominio/activo') ?>">
                        <?= csrf_field() ?>
                        <label for="condo-switch">Condominio:</label>
                        <select id="condo-switch" name="condominio_id" onchange="this.form.submit()">
                            <?php foreach ($allowed as $c): ?>
                                <option value="<?= (int) $c['id'] ?>" <?= (int) $c['id'] === $activeCondo ? 'selected' : '' ?>>
                                    <?= esc($c['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php endif; ?>
                <a href="<?= site_url('notificaciones') ?>" class="bell" title="Notificaciones">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <?php if ($unread > 0): ?>
                        <span class="badge"><?= $unread > 9 ? '9+' : $unread ?></span>
                    <?php endif; ?>
                </a>
                <details class="user-menu">
                    <summary>
                        <span class="user-glyph" aria-hidden="true">👤</span>
                        <span class="email-text"><?= esc(auth()->user()->email ?? auth()->user()->username) ?></span>
                        <span style="font-size:.7rem;">▾</span>
                    </summary>
                    <div class="menu">
                        <a href="<?= site_url('notificaciones/preferencias') ?>">Configuración de notificaciones</a>
                        <a href="<?= site_url('logout') ?>">Cerrar sesión</a>
                    </div>
                </details>
            </div>
            <nav class="mainnav">
                <?php foreach ($menu as $item): ?>
                    <?php if ($item['show']): ?>
                        <a href="<?= $item['url'] ?>" class="<?= $item['active'] ? 'active' : '' ?>"><?= esc($item['label']) ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
    </header>
    <main>
        <?php if ($isAuthed && env('push.publicKey')): ?>
            <div class="notif-banner" id="notif-banner">
                <span>🔔</span>
                <span id="notif-banner-text">Activa las notificaciones para enterarte al instante cuando llegue una visita.</span>
                <span class="nb-actions">
                    <a class="nb-cta" id="notif-banner-cta" href="<?= site_url('notificaciones/preferencias') ?>">Activar</a>
                    <button type="button" class="nb-close" id="notif-banner-close" title="Ocultar">✕</button>
                </span>
            </div>
        <?php endif; ?>

        <?php foreach (['error', 'success'] as $type): ?>
            <?php if (session($type)): ?>
                <div class="alert <?= $type ?>"><?= esc(session($type)) ?></div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?= $this->renderSection('content') ?>
    </main>

    <?php if ($isAuthed && env('push.publicKey')): ?>
    <script>
    (function () {
        var banner = document.getElementById('notif-banner');
        if (! banner) { return; }
        var txt = document.getElementById('notif-banner-text');
        var cta = document.getElementById('notif-banner-cta');
        var closeBtn = document.getElementById('notif-banner-close');
        var supported = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;

        // User dismissed it this session? keep it hidden.
        if (sessionStorage.getItem('kaan_notif_banner_dismissed') === '1') { return; }

        if (! supported) { return; } // no browser support → nothing to nag about
        var perm = Notification.permission;
        if (perm === 'granted') { return; } // already on

        if (perm === 'denied') {
            txt.textContent = 'Las notificaciones están bloqueadas en este navegador. Actívalas desde la configuración del sitio (icono del candado en la barra de direcciones) para recibir avisos.';
            cta.textContent = 'Cómo activar';
        }
        banner.style.display = 'flex';

        closeBtn.addEventListener('click', function () {
            banner.style.display = 'none';
            sessionStorage.setItem('kaan_notif_banner_dismissed', '1');
        });
    })();
    </script>
    <?php endif; ?>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
