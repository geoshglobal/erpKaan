<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php use App\Models\PersonaModel; ?>

<div class="page-head">
    <h1>Personas</h1>
    <a class="btn" href="<?= site_url('personas/nueva') ?>">+ Nueva persona</a>
</div>

<?php if ($personas === []): ?>
    <p class="muted">No hay personas registradas en este condominio todavía.</p>
<?php else: ?>
    <table class="grid">
        <thead>
            <tr>
                <th style="width:48px;"></th>
                <th>Nombre</th>
                <th>Contacto</th>
                <th>RFC</th>
                <th>Estado</th>
                <th style="text-align:right;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($personas as $p): ?>
                <tr>
                    <td>
                        <?php if (! empty($p['foto_path'])): ?>
                            <img src="<?= base_url(esc($p['foto_path'])) ?>" alt=""
                                 style="width:36px; height:36px; border-radius:50%; object-fit:cover;">
                        <?php else: ?>
                            <span style="display:inline-flex; width:36px; height:36px; border-radius:50%; background:#e2e8f0;
                                         align-items:center; justify-content:center; color:#64748b; font-weight:700;">
                                <?= esc(mb_strtoupper(mb_substr($p['nombre'], 0, 1))) ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= esc(PersonaModel::fullName($p)) ?></strong></td>
                    <td class="muted">
                        <?= esc($p['email'] ?: '') ?><?= $p['email'] && $p['telefono'] ? ' · ' : '' ?><?= esc($p['telefono'] ?: '') ?>
                        <?= ! $p['email'] && ! $p['telefono'] ? '—' : '' ?>
                    </td>
                    <td class="muted"><?= esc($p['rfc'] ?: '—') ?></td>
                    <td><span class="pill <?= $p['activo'] ? 'on' : 'off' ?>"><?= $p['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a class="btn secondary small" href="<?= site_url('personas/' . $p['id'] . '/editar') ?>">Editar</a>
                        <form method="post" action="<?= site_url('personas/' . $p['id'] . '/eliminar') ?>"
                              style="display:inline;" onsubmit="return confirm('¿Eliminar esta persona?');">
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
