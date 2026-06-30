<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?= $this->include('partials/propiedades_nav', ['current' => 'casas']) ?>

<?php $usos = ['propio' => 'Uso propio', 'renta_lineal' => 'Renta lineal', 'renta_vacacional' => 'Renta vacacional']; ?>

<div class="page-head">
    <div>
        <h1 style="margin-bottom:.2rem;">Ocupación</h1>
        <span class="muted">Casa <strong><?= esc($casa['identificador']) ?></strong></span>
    </div>
    <div>
        <a class="btn secondary" href="<?= site_url('casas') ?>">← Casas</a>
        <a class="btn" href="<?= site_url('casas/' . $casa['id'] . '/ocupaciones/nueva') ?>">+ Nueva ocupación</a>
    </div>
</div>

<?php if (session('error')): ?><div class="alert error"><?= esc(session('error')) ?></div><?php endif; ?>
<?php if (session('success')): ?><div class="alert success"><?= esc(session('success')) ?></div><?php endif; ?>

<?php if ($ocupaciones === []): ?>
    <p class="muted">Esta casa no tiene ocupaciones registradas.</p>
<?php else: ?>
    <table class="grid">
        <thead>
            <tr>
                <th>Uso</th>
                <th>Desde</th>
                <th>Hasta</th>
                <th>Renta</th>
                <th>Ocupantes</th>
                <th>Vigente</th>
                <th style="text-align:right;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ocupaciones as $o): ?>
                <tr>
                    <td><strong><?= esc($usos[$o['tipo_uso']] ?? $o['tipo_uso']) ?></strong></td>
                    <td class="muted"><?= esc($o['fecha_inicio'] ?: '—') ?></td>
                    <td class="muted"><?= esc($o['fecha_fin'] ?: '—') ?></td>
                    <td><?= $o['renta_monto'] !== null ? '$' . esc(number_format((float) $o['renta_monto'], 2)) : '—' ?></td>
                    <td><?= (int) $o['num_ocupantes'] ?></td>
                    <td><?= (int) $o['vigente'] === 1 ? '<span class="pill on">Vigente</span>' : '<span class="muted">—</span>' ?></td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a class="btn secondary small" href="<?= site_url('casas/' . $casa['id'] . '/ocupaciones/' . $o['id'] . '/editar') ?>">Abrir</a>
                        <form method="post" action="<?= site_url('casas/' . $casa['id'] . '/ocupaciones/' . $o['id'] . '/eliminar') ?>"
                              style="display:inline;" onsubmit="return confirm('¿Eliminar esta ocupación?');">
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
