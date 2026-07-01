<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $this->renderSection('title') ?> · erpKaan</title>
    <meta name="theme-color" content="#1C2621">
    <link rel="icon" href="<?= base_url('brand/favicon.ico') ?>" sizes="any">
    <link rel="apple-touch-icon" href="<?= base_url('brand/png/erpKaan-isotipo-256.png') ?>">

    <!-- Bootstrap (Shield auth forms) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --accent:#2C6E52; --accent2:#43A074; --cream:#F6F4ED; --ink:#1C2621; --line:#dbe5de; }
        body { background:var(--cream); color:var(--ink); font-family:'Plus Jakarta Sans', system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
        .auth-brand { text-align:center; margin:2.2rem 0 1rem; }
        .auth-brand img { height:44px; width:auto; }
        .card { border:1px solid var(--line); border-radius:16px; box-shadow:0 10px 30px rgba(28,38,33,.06); }
        .card-header { background:#fff; border-bottom:1px solid var(--line); font-weight:800; }
        h1, h2, h3, .h1, .h2, .h3, .card-header { letter-spacing:-.01em; }
        .btn-primary { --bs-btn-bg:var(--accent); --bs-btn-border-color:var(--accent); --bs-btn-hover-bg:var(--accent2); --bs-btn-hover-border-color:var(--accent2); --bs-btn-active-bg:var(--accent2); --bs-btn-active-border-color:var(--accent2); font-weight:700; }
        a { color:var(--accent); }
        a:hover { color:var(--accent2); }
        .form-control:focus { border-color:var(--accent2); box-shadow:0 0 0 .2rem rgba(67,160,116,.25); }
        .auth-foot { text-align:center; color:#6b7a70; font-size:.8rem; margin:1.2rem 0 2rem; }
    </style>
    <?= $this->renderSection('pageStyles') ?>
</head>
<body>
    <div class="auth-brand">
        <a href="<?= site_url('/') ?>"><img src="<?= base_url('brand/svg/erpKaan-logotipo-horizontal.svg') ?>" alt="erpKaan"></a>
    </div>
    <main role="main" class="container">
        <?= $this->renderSection('main') ?>
    </main>
    <p class="auth-foot">Administración de condominios · erpKaan</p>

<?= $this->renderSection('pageScripts') ?>
</body>
</html>
