<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <h1>Entregar paquete</h1>
    <a class="btn secondary" href="<?= site_url('accesos/' . $acceso['id']) ?>">← Acceso</a>
</div>

<?php if (session('errors')): ?>
    <div class="alert error"><ul style="margin:0; padding-left:1.1rem;">
        <?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul></div>
<?php endif; ?>

<div class="card" style="margin-bottom:1rem;">
    <strong><?= esc($acceso['nombre_visitante']) ?></strong>
    <?= $acceso['empresa'] ? '<span class="muted">— ' . esc($acceso['empresa']) . '</span>' : '' ?>
</div>

<form class="card" method="post" action="<?= site_url('caseta/accesos/' . $acceso['id'] . '/entregar') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="field">
        <label>Foto de la entrega (quién recibe / evidencia)</label>
        <input type="file" name="foto" id="foto" accept="image/*" capture="environment">
        <?= $this->include('partials/camera_capture', ['inputId' => 'foto']) ?>
        <div class="muted" style="font-size:.78rem; margin-top:.25rem;">Opcional pero recomendado como evidencia de entrega.</div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn">📬 Confirmar entrega</button>
        <a class="btn secondary" href="<?= site_url('accesos/' . $acceso['id']) ?>">Cancelar</a>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/camera.js') ?>"></script>
<?= $this->endSection() ?>
