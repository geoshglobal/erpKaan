<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div style="max-width:440px; margin:2.5rem auto; text-align:center;">
    <h1>Pase de otro condominio</h1>
    <p class="muted">Este pase no pertenece al condominio que tienes activo. Cambia de
        condominio en la barra superior para poder verlo y operarlo.</p>
    <a class="btn secondary" href="<?= site_url('accesos') ?>">← Accesos</a>
</div>

<?= $this->endSection() ?>
