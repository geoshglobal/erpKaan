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
        header.topbar .brand { font-weight:700; letter-spacing:.5px; }
        header.topbar .brand span { color:#5eead4; }
        header.topbar .user { display:flex; align-items:center; gap:.75rem; font-size:.9rem; }
        header.topbar .user a { color:#cbd5e1; text-decoration:none; }
        header.topbar .user a:hover { color:#fff; }
        main { max-width: 1040px; margin: 1.5rem auto; padding: 0 1.25rem; }
        .alert { padding:.75rem 1rem; border-radius:8px; margin-bottom:1rem; font-size:.9rem; }
        .alert.error { background:#fee2e2; color:#991b1b; }
        .alert.success { background:#dcfce7; color:#166534; }
        .badge { display:inline-block; background:#ccfbf1; color:#0f766e; border-radius:999px; padding:.15rem .6rem; font-size:.75rem; font-weight:600; }
        .btn { background:var(--accent); color:#fff; border:none; padding:.5rem .9rem; border-radius:8px; text-decoration:none; font-size:.85rem; cursor:pointer; }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="brand">erp<span>Kaan</span></div>
        <?php if (function_exists('auth') && auth()->loggedIn()): ?>
            <div class="user">
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
</body>
</html>
