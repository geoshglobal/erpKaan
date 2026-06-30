<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <h1>Condominios</h1>
    <a class="btn" href="<?= site_url('condominios/nuevo') ?>">+ Nuevo condominio</a>
</div>

<?php if ($condominios === []): ?>
    <p class="muted">Aún no hay condominios. Crea el primero.</p>
<?php else: ?>
    <table class="grid">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Slug</th>
                <th>Ubicación</th>
                <th>Moneda</th>
                <th>Estado</th>
                <th style="text-align:right;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($condominios as $c): ?>
                <tr>
                    <td><strong><?= esc($c['nombre']) ?></strong></td>
                    <td class="muted"><?= esc($c['slug']) ?></td>
                    <td class="muted">
                        <?= esc(trim(($c['municipio'] ?? '') . ($c['estado'] ? ', ' . $c['estado'] : ''), ', ')) ?: '—' ?>
                    </td>
                    <td><?= esc($c['moneda']) ?></td>
                    <td>
                        <span class="pill <?= $c['activo'] ? 'on' : 'off' ?>">
                            <?= $c['activo'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a class="btn secondary small" href="<?= site_url('condominios/' . $c['id'] . '/editar') ?>">Editar</a>
                        <form method="post" action="<?= site_url('condominios/' . $c['id'] . '/eliminar') ?>"
                              style="display:inline;" onsubmit="return confirm('¿Eliminar este condominio?');">
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
