<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'erpKaan') ?></title>
    <style>
        :root { --bg:#0f172a; --panel:#fff; --muted:#64748b; --accent:#0d9488; --line:#e2e8f0; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background:#f1f5f9; color:#0f172a; }
        header.topbar { background:var(--bg); color:#fff; display:flex; align-items:center; justify-content:space-between; padding:0 1.25rem; height:56px; }
        header.topbar .topbar-left { display:flex; align-items:center; gap:1.25rem; height:100%; }
        header.topbar .brand { font-weight:700; letter-spacing:.5px; color:#fff; text-decoration:none; }
        header.topbar .brand span { color:#5eead4; }
        header.topbar nav.mainnav { display:flex; align-items:center; gap:.25rem; height:100%; }
        header.topbar nav.mainnav a { color:#cbd5e1; text-decoration:none; font-size:.9rem; padding:.4rem .7rem; border-radius:8px; line-height:1; }
        header.topbar nav.mainnav a:hover { color:#fff; background:#1e293b; }
        header.topbar nav.mainnav a.active { color:#fff; background:#0d9488; font-weight:600; }
        header.topbar .user { display:flex; align-items:center; gap:.9rem; font-size:.9rem; }
        header.topbar .user a { color:#cbd5e1; text-decoration:none; }
        header.topbar .user a:hover { color:#fff; }
        .bell { position:relative; display:inline-flex; align-items:center; color:#cbd5e1; }
        .bell:hover { color:#fff; }
        .bell .badge { position:absolute; top:-7px; right:-9px; background:#ef4444; color:#fff; border-radius:999px; font-size:.62rem; font-weight:700; padding:.05rem .3rem; }
        .user-menu { position:relative; }
        .user-menu summary { list-style:none; cursor:pointer; color:#cbd5e1; display:inline-flex; align-items:center; gap:.3rem; }
        .user-menu summary::-webkit-details-marker { display:none; }
        .user-menu summary:hover { color:#fff; }
        .user-menu[open] summary { color:#fff; }
        .user-menu .menu { position:absolute; right:0; top:150%; background:#fff; color:#0f172a; border:1px solid var(--line); border-radius:8px; min-width:170px; box-shadow:0 8px 24px rgba(0,0,0,.14); z-index:30; overflow:hidden; }
        .user-menu .menu a { display:block; padding:.6rem .9rem; color:#0f172a; font-size:.88rem; }
        .user-menu .menu a:hover { background:#f1f5f9; color:#0f172a; }
        main { max-width: 1040px; margin: 1.5rem auto; padding: 0 1.25rem; }
        .alert { padding:.75rem 1rem; border-radius:8px; margin-bottom:1rem; font-size:.9rem; }
        .alert.error { background:#fee2e2; color:#991b1b; }
        .alert.success { background:#dcfce7; color:#166534; }
        .badge { display:inline-block; background:#ccfbf1; color:#0f766e; border-radius:999px; padding:.15rem .6rem; font-size:.75rem; font-weight:600; }
        .btn { background:var(--accent); color:#fff; border:none; padding:.5rem .9rem; border-radius:8px; text-decoration:none; font-size:.85rem; cursor:pointer; display:inline-block; }
        .btn:hover { filter:brightness(.95); }
        .btn.secondary { background:#e2e8f0; color:#0f172a; }
        .btn.danger { background:#ef4444; }
        .btn.small { padding:.3rem .6rem; font-size:.78rem; }
        .page-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem; }
        .page-head h1 { margin:0; font-size:1.4rem; }
        table.grid { width:100%; border-collapse:collapse; background:#fff; border:1px solid var(--line); border-radius:12px; overflow:hidden; }
        table.grid th, table.grid td { text-align:left; padding:.7rem .9rem; border-bottom:1px solid var(--line); font-size:.9rem; }
        table.grid th { background:#f8fafc; color:#475569; font-weight:600; }
        table.grid tr:last-child td { border-bottom:none; }
        .muted { color:var(--muted); }
        .pill { display:inline-block; border-radius:999px; padding:.1rem .55rem; font-size:.72rem; font-weight:600; }
        .pill.on { background:#dcfce7; color:#166534; }
        .pill.off { background:#fee2e2; color:#991b1b; }
        form.card { background:#fff; border:1px solid var(--line); border-radius:12px; padding:1.25rem; }
        .field { margin-bottom:1rem; }
        .field label { display:block; font-size:.82rem; font-weight:600; margin-bottom:.3rem; color:#334155; }
        .field input[type=text], .field input[type=email], .field input[type=number], .field input[type=password], .field input[type=date], .field input[type=datetime-local], .field select {
            width:100%; padding:.5rem .6rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.9rem; }
        .stepper { display:inline-flex; align-items:stretch; border:1px solid #cbd5e1; border-radius:8px; overflow:hidden; background:#fff; }
        .stepper button { border:none; background:#f1f5f9; color:#0f172a; width:40px; font-size:1.2rem; line-height:1; cursor:pointer; }
        .stepper button:hover { background:#e2e8f0; }
        .stepper input { border:none; width:64px; text-align:center; font-size:1rem; padding:.5rem 0; -moz-appearance:textfield; appearance:textfield; }
        .stepper input::-webkit-outer-spin-button, .stepper input::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
        .grid2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .form-actions { display:flex; gap:.6rem; margin-top:1rem; }
        fieldset { border:1px solid var(--line); border-radius:10px; padding:1rem; margin:0 0 1.25rem; }
        legend { font-size:.8rem; font-weight:700; color:#0f766e; padding:0 .4rem; }
        .tenant-select { display:flex; align-items:center; gap:.4rem; }
        .tenant-select select { background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; padding:.3rem .5rem; font-size:.85rem; }
        .notif-banner { display:none; align-items:center; gap:.75rem; background:#fffbeb; border:1px solid #fde68a; color:#92400e; border-radius:10px; padding:.7rem 1rem; margin-bottom:1rem; font-size:.88rem; }
        .notif-banner .nb-actions { margin-left:auto; display:flex; gap:.5rem; align-items:center; white-space:nowrap; }
        .notif-banner a.nb-cta { background:#0d9488; color:#fff; text-decoration:none; padding:.35rem .7rem; border-radius:8px; font-weight:600; }
        .notif-banner button.nb-close { background:none; border:none; color:#92400e; cursor:pointer; font-size:1.1rem; line-height:1; }
    </style>
    <?= $this->renderSection('head') ?>
</head>
<body>
    <?php $isAuthed = function_exists('auth') && auth()->loggedIn(); ?>
    <header class="topbar">
        <div class="topbar-left">
            <a class="brand" href="<?= site_url($isAuthed ? 'dashboard' : '/') ?>">erp<span>Kaan</span></a>
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
                ?>
                <nav class="mainnav">
                    <?php foreach ($menu as $item): ?>
                        <?php if ($item['show']): ?>
                            <a href="<?= $item['url'] ?>" class="<?= $item['active'] ? 'active' : '' ?>"><?= esc($item['label']) ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
        </div>
        <?php if ($isAuthed): ?>
            <?php
            $tenant      = service('tenant');
            $allowed     = $tenant->allowedCondominios();
            $activeCondo = $tenant->activeId();
            ?>
            <div class="user">
                <?php if ($allowed !== []): ?>
                    <form class="tenant-select" method="post" action="<?= site_url('condominio/activo') ?>">
                        <?= csrf_field() ?>
                        <label for="condo-switch" style="color:#94a3b8; font-size:.8rem;">Condominio:</label>
                        <select id="condo-switch" name="condominio_id" onchange="this.form.submit()">
                            <?php foreach ($allowed as $c): ?>
                                <option value="<?= (int) $c['id'] ?>" <?= (int) $c['id'] === $activeCondo ? 'selected' : '' ?>>
                                    <?= esc($c['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php endif; ?>
                <?php $unread = (new \App\Models\NotificacionModel())->unreadCount((int) auth()->id()); ?>
                <a href="<?= site_url('notificaciones') ?>" class="bell" title="Notificaciones">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <?php if ($unread > 0): ?>
                        <span class="badge"><?= $unread > 9 ? '9+' : $unread ?></span>
                    <?php endif; ?>
                </a>
                <details class="user-menu">
                    <summary><?= esc(auth()->user()->email ?? auth()->user()->username) ?> <span style="font-size:.7rem;">▾</span></summary>
                    <div class="menu">
                        <a href="<?= site_url('notificaciones/preferencias') ?>">Configuración de notificaciones</a>
                        <a href="<?= site_url('logout') ?>">Cerrar sesión</a>
                    </div>
                </details>
            </div>
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
