<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div style="max-width:460px; margin:2.5rem auto; text-align:center;">
    <h1>Invitación no válida</h1>
    <p class="muted">Este enlace de invitación no existe, ya fue utilizado o expiró.
        Pide a la administración del condominio que genere uno nuevo.</p>
    <a class="btn secondary" href="<?= site_url('login') ?>">Ir a iniciar sesión</a>
</div>

<?= $this->endSection() ?>
