<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?= $this->include('partials/propiedades_nav', ['current' => 'casas']) ?>

<?php
$usoLabels = [
    'propio'           => 'Uso propio',
    'renta_lineal'     => 'Renta lineal',
    'renta_vacacional' => 'Renta vacacional',
];
?>

<div class="page-head">
    <h1>Casas</h1>
    <a class="btn" href="<?= site_url('casas/nueva') ?>">+ Nueva casa</a>
</div>

<?php if ($casas === []): ?>
    <p class="muted">No hay casas en este condominio todavía.</p>
<?php else: ?>
    <table class="grid">
        <thead>
            <tr>
                <th>Identificador</th>
                <th>Torre</th>
                <th>Uso actual</th>
                <th>Cajones</th>
                <th>m²</th>
                <th>Estado</th>
                <th style="text-align:right;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($casas as $c): ?>
                <tr>
                    <td><strong><?= esc($c['identificador']) ?></strong></td>
                    <td class="muted"><?= esc($c['torre_nombre'] ?? '—') ?: '—' ?></td>
                    <td><?= esc($usoLabels[$c['tipo_ocupacion_actual']] ?? $c['tipo_ocupacion_actual']) ?></td>
                    <td><?= (int) $c['num_cajones'] ?></td>
                    <td><?= $c['m2'] !== null ? esc($c['m2']) : '—' ?></td>
                    <td><span class="pill <?= $c['activo'] ? 'on' : 'off' ?>"><?= $c['activo'] ? 'Activa' : 'Inactiva' ?></span></td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a class="btn secondary small" href="<?= site_url('casas/' . $c['id'] . '/propietarios') ?>">Dueños</a>
                        <a class="btn secondary small" href="<?= site_url('casas/' . $c['id'] . '/editar') ?>">Editar</a>
                        <form method="post" action="<?= site_url('casas/' . $c['id'] . '/eliminar') ?>"
                              style="display:inline;" onsubmit="return confirm('¿Eliminar esta casa?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn danger small">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?= $this->endSection() ?>
