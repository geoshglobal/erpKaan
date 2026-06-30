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
            <div style="padding:.9rem 1.1rem; border-bottom:1px solid #e2e8f0; <?= $n['leido_at'] === null ? 'background:#f0fdfa;' : '' ?>">
                <div style="display:flex; justify-content:space-between; gap:1rem;">
                    <strong><?= esc($n['titulo']) ?></strong>
                    <span class="muted" style="font-size:.78rem; white-space:nowrap;"><?= esc(date('d/m/Y H:i', strtotime($n['created_at']))) ?></span>
                </div>
                <?php if ($n['mensaje']): ?><div class="muted" style="font-size:.9rem; margin-top:.2rem;"><?= esc($n['mensaje']) ?></div><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
