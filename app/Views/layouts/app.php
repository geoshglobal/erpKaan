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
        header.topbar .user { display:flex; align-items:center; gap:.75rem; font-size:.9rem; }
        header.topbar .user a { color:#cbd5e1; text-decoration:none; }
        header.topbar .user a:hover { color:#fff; }
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
        .field input[type=text], .field input[type=email], .field select {
            width:100%; padding:.5rem .6rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.9rem; }
        .grid2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .form-actions { display:flex; gap:.6rem; margin-top:1rem; }
        fieldset { border:1px solid var(--line); border-radius:10px; padding:1rem; margin:0 0 1.25rem; }
        legend { font-size:.8rem; font-weight:700; color:#0f766e; padding:0 .4rem; }
        .tenant-select { display:flex; align-items:center; gap:.4rem; }
        .tenant-select select { background:#1e293b; color:#fff; border:1px solid #334155; border-radius:8px; padding:.3rem .5rem; font-size:.85rem; }
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
                <span><?= esc(auth()->user()->email ?? auth()->user()->username) ?></span>
                <a href="<?= site_url('logout') ?>">Cerrar sesión</a>
            </div>
        <?php endif; ?>
    </header>
    <main>
        <?php foreach (['error', 'success'] as $type): ?>
            <?php if (session($type)): ?>
                <div class="alert <?= $type ?>"><?= esc(session($type)) ?></div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?= $this->renderSection('content') ?>
    </main>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
