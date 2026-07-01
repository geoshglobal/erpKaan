<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <h1>Notificaciones</h1>
</div>

<?php if ($items === []): ?>
    <p class="muted">No tienes notificaciones.</p>
<?php else: ?>
    <div class="card" style="padding:0;">
        <?php foreach ($items as $n): ?>
            <?php $tag = ! empty($n['url']) ? 'a' : 'div'; ?>
            <<?= $tag ?> <?= ! empty($n['url']) ? 'href="' . esc($n['url']) . '"' : '' ?>
               style="display:block; text-decoration:none; color:inherit; padding:.9rem 1.1rem; border-bottom:1px solid #e2e8f0; <?= $n['leido_at'] === null ? 'background:#f0fdfa;' : '' ?>">
                <div style="display:flex; justify-content:space-between; gap:1rem;">
                    <strong><?= esc($n['titulo']) ?><?= ! empty($n['url']) ? ' <span class="muted" style="font-weight:400;">›</span>' : '' ?></strong>
                    <span class="muted" style="font-size:.78rem; white-space:nowrap;"><?= esc(date('d/m/Y H:i', strtotime($n['created_at']))) ?></span>
                </div>
                <?php if ($n['mensaje']): ?><div class="muted" style="font-size:.9rem; margin-top:.2rem;"><?= esc($n['mensaje']) ?></div><?php endif; ?>
            </<?= $tag ?>>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
