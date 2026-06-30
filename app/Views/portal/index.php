<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
use App\Models\PersonaModel;

$usos = ['propio' => 'Uso propio', 'renta_lineal' => 'Renta lineal', 'renta_vacacional' => 'Renta vacacional'];
?>

<div class="page-head">
    <h1>Mi portal</h1>
</div>

<?php if (session('success')): ?><div class="alert success"><?= esc(session('success')) ?></div><?php endif; ?>

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
                        <li><strong><?= esc($o['identificador']) ?></strong>
                            <span class="muted">— <?= esc($usos[$o['tipo_uso']] ?? $o['tipo_uso']) ?>, <?= esc($o['rol']) ?></span></li>
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

    <p class="muted" style="margin-top:1.5rem; font-size:.85rem;">
        Próximamente aquí podrás generar pases QR de visita y avisar paquetería. 🚧
    </p>
<?php endif; ?>

<?= $this->endSection() ?>
