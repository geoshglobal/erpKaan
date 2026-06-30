<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?= $this->include('partials/propiedades_nav', ['current' => 'torres']) ?>

<div class="page-head">
    <h1>Torres</h1>
    <a class="btn" href="<?= site_url('torres/nueva') ?>">+ Nueva torre</a>
</div>

<?php if ($torres === []): ?>
    <p class="muted">No hay torres en este condominio todavía.</p>
<?php else: ?>
    <table class="grid">
        <thead>
            <tr>
                <th>Clave</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Orden</th>
                <th>Estado</th>
                <th style="text-align:right;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($torres as $t): ?>
                <tr>
                    <td><?= esc($t['clave'] ?: '—') ?></td>
                    <td><strong><?= esc($t['nombre']) ?></strong></td>
                    <td class="muted"><?= esc($t['descripcion'] ?: '—') ?></td>
                    <td><?= (int) $t['orden'] ?></td>
                    <td><span class="pill <?= $t['activo'] ? 'on' : 'off' ?>"><?= $t['activo'] ? 'Activa' : 'Inactiva' ?></span></td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a class="btn secondary small" href="<?= site_url('torres/' . $t['id'] . '/editar') ?>">Editar</a>
                        <form method="post" action="<?= site_url('torres/' . $t['id'] . '/eliminar') ?>"
                              style="display:inline;" onsubmit="return confirm('¿Eliminar esta torre?');">
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
