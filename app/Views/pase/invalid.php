<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div style="max-width:440px; margin:2.5rem auto; text-align:center;">
    <h1>Pase no válido</h1>
    <p class="muted">Este pase de acceso no existe o fue revocado.</p>
</div>

<?= $this->endSection() ?>
