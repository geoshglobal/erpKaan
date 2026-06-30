<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div style="margin-bottom:1.5rem;">
    <h1 style="margin:0 0 .25rem;">Hola, <?= esc($user->username ?? $user->email) ?> 👋</h1>
    <p style="color:#64748b; margin:.25rem 0;">
        Rol(es):
        <?php foreach ($groups as $g): ?>
            <span class="badge"><?= esc($g) ?></span>
        <?php endforeach; ?>
    </p>
</div>

<?php if ($modules === []): ?>
    <div class="alert error">Tu usuario no tiene módulos asignados. Contacta al administrador.</div>
<?php else: ?>
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:1rem;">
        <?php foreach ($modules as $m): ?>
            <a href="<?= esc($m['url']) ?>" style="text-decoration:none; color:inherit;">
                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:1.1rem; height:100%; transition:box-shadow .15s;"
                     onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,.08)'" onmouseout="this.style.boxShadow='none'">
                    <div style="font-weight:700; margin-bottom:.35rem;"><?= esc($m['title']) ?></div>
                    <div style="font-size:.85rem; color:#64748b;"><?= esc($m['desc']) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
