<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <h1>Autorizaciones de cajón</h1>
    <a class="btn secondary" href="<?= site_url('portal') ?>">← Mi portal</a>
</div>

<?php if ($pendientes === []): ?>
    <p class="muted">No tienes solicitudes de autorización pendientes.</p>
<?php else: ?>
    <?php foreach ($pendientes as $a): ?>
        <div class="card" style="margin-bottom:1rem;">
            <p style="margin:0 0 .5rem;">
                <strong><?= esc($a['nombre_visitante']) ?></strong>
                <span class="muted">— casa <?= esc($a['casa_ident'] ?? '') ?><?= $a['placas'] ? ', placas ' . esc($a['placas']) : '' ?></span>
            </p>
            <p class="muted" style="margin:0 0 .75rem; font-size:.9rem;">
                No hay cajones de visita disponibles. ¿Autorizas usar tu cajón para esta visita?
            </p>
            <div style="display:flex; gap:.5rem;">
                <form method="post" action="<?= site_url('portal/autorizaciones/' . $a['id']) ?>" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="decision" value="autorizar">
                    <button class="btn">Autorizar</button>
                </form>
                <form method="post" action="<?= site_url('portal/autorizaciones/' . $a['id']) ?>" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="decision" value="rechazar">
                    <button class="btn danger">Rechazar</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
