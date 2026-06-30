<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
use App\Models\PersonaModel;

$usos = ['propio' => 'Uso propio', 'renta_lineal' => 'Renta lineal', 'renta_vacacional' => 'Renta vacacional'];
?>

<div class="page-head">
    <h1>Mi portal</h1>
    <?php if ($persona !== null): ?>
        <a class="btn secondary" href="<?= site_url('portal/perfil') ?>">Editar mi perfil</a>
    <?php endif; ?>
</div>


<?php if ($persona === null): ?>
    <div class="alert error">Tu usuario no está vinculado a una persona del condominio. Contacta a la administración.</div>
<?php else: ?>
    <div class="card" style="margin-bottom:1.25rem;">
        <h2 style="margin:0 0 .25rem; font-size:1.1rem;">Hola, <?= esc(PersonaModel::fullName($persona)) ?> 👋</h2>
        <p class="muted" style="margin:.2rem 0;">
            <?= esc($persona['email'] ?: '') ?><?= $persona['email'] && $persona['telefono'] ? ' · ' : '' ?><?= esc($persona['telefono'] ?: '') ?>
        </p>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
        <div class="card">
            <h3 style="margin:0 0 .6rem; font-size:1rem;">🏠 Donde habito</h3>
            <?php if ($ocupaciones === []): ?>
                <p class="muted" style="margin:0;">Sin ocupaciones vigentes registradas.</p>
            <?php else: ?>
                <ul style="margin:0; padding-left:1.1rem;">
                    <?php foreach ($ocupaciones as $o): ?>
                        <li style="margin-bottom:.35rem;"><strong><?= esc($o['identificador']) ?></strong>
                            <span class="muted">— <?= esc($usos[$o['tipo_uso']] ?? $o['tipo_uso']) ?>, <?= esc($o['rol']) ?></span>
                            <?php if ($o['rol'] === 'principal'): ?>
                                · <a href="<?= site_url('portal/ocupacion/' . $o['ocupacion_id'] . '/ocupantes') ?>" style="font-size:.85rem;">Gestionar ocupantes</a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3 style="margin:0 0 .6rem; font-size:1rem;">🔑 Propiedades</h3>
            <?php if ($propiedades === []): ?>
                <p class="muted" style="margin:0;">No eres propietario de ninguna casa.</p>
            <?php else: ?>
                <ul style="margin:0; padding-left:1.1rem;">
                    <?php foreach ($propiedades as $p): ?>
                        <li><strong><?= esc($p['identificador']) ?></strong>
                            <span class="muted">— <?= esc(rtrim(rtrim((string) $p['porcentaje'], '0'), '.')) ?>%<?= (int) $p['principal'] === 1 ? ', principal' : '' ?></span></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" style="margin-top:1rem;">
        <h3 style="margin:0 0 .5rem; font-size:1rem;">🎫 Visitas</h3>
        <p class="muted" style="margin:.2rem 0 .75rem; font-size:.9rem;">Genera pases QR para tus visitantes.</p>
        <a class="btn" href="<?= site_url('portal/visitas') ?>">Mis visitas</a>
        <a class="btn secondary" href="<?= site_url('portal/visitas/nueva') ?>">+ Nueva visita</a>
    </div>

    <p class="muted" style="margin-top:1.5rem; font-size:.85rem;">
        Próximamente: avisos de paquetería y delivery. 🚧
    </p>
<?php endif; ?>

<?= $this->endSection() ?>
