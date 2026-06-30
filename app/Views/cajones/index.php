<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?= $this->include('partials/propiedades_nav', ['current' => 'cajones']) ?>

<?php
$tipoLabels = ['asignado' => 'Asignado', 'visita' => 'Visita', 'comun' => 'Común'];
$techadoLabel = static fn ($v) => $v === null ? '—' : ((int) $v === 1 ? 'Techado' : 'Descubierto');
?>

<div class="page-head">
    <h1>Cajones de estacionamiento</h1>
    <a class="btn" href="<?= site_url('cajones/nuevo') ?>">+ Nuevo cajón</a>
</div>

<?php if ($cajones === []): ?>
    <p class="muted">No hay cajones en este condominio todavía.</p>
<?php else: ?>
    <table class="grid">
        <thead>
            <tr>
                <th>Identificador</th>
                <th>Tipo</th>
                <th>Casa asignada</th>
                <th>Techado</th>
                <th>Estado</th>
                <th style="text-align:right;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cajones as $c): ?>
                <tr>
                    <td><strong><?= esc($c['identificador']) ?></strong></td>
                    <td><?= esc($tipoLabels[$c['tipo']] ?? $c['tipo']) ?></td>
                    <td class="muted"><?= esc($c['casa_ident'] ?? '') ?: '—' ?></td>
                    <td class="muted"><?= esc($techadoLabel($c['techado'])) ?></td>
                    <td><span class="pill <?= $c['activo'] ? 'on' : 'off' ?>"><?= $c['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a class="btn secondary small" href="<?= site_url('cajones/' . $c['id'] . '/editar') ?>">Editar</a>
                        <form method="post" action="<?= site_url('cajones/' . $c['id'] . '/eliminar') ?>"
                              style="display:inline;" onsubmit="return confirm('¿Eliminar este cajón?');">
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
