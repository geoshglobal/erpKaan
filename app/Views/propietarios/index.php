<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php use App\Models\PersonaModel; ?>

<?= $this->include('partials/propiedades_nav', ['current' => 'casas']) ?>

<div class="page-head">
    <div>
        <h1 style="margin-bottom:.2rem;">Propietarios</h1>
        <span class="muted">Casa <strong><?= esc($casa['identificador']) ?></strong></span>
    </div>
    <a class="btn secondary" href="<?= site_url('casas') ?>">← Casas</a>
</div>

<?php if (session('error')): ?><div class="alert error"><?= esc(session('error')) ?></div><?php endif; ?>
<?php if (session('errors')): ?>
    <div class="alert error"><ul style="margin:0; padding-left:1.1rem;">
        <?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul></div>
<?php endif; ?>

<?php if ($owners === []): ?>
    <p class="muted">Esta casa no tiene propietarios registrados.</p>
<?php else: ?>
    <table class="grid" style="margin-bottom:1.5rem;">
        <thead>
            <tr>
                <th>Propietario</th>
                <th>%</th>
                <th>Desde</th>
                <th>Hasta</th>
                <th>Principal</th>
                <th style="text-align:right;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($owners as $o): ?>
                <tr>
                    <td><strong><?= esc(PersonaModel::fullName($o)) ?></strong></td>
                    <td><?= esc(rtrim(rtrim((string) $o['porcentaje'], '0'), '.')) ?>%</td>
                    <td class="muted"><?= esc($o['fecha_inicio'] ?: '—') ?></td>
                    <td class="muted"><?= esc($o['fecha_fin'] ?: '—') ?></td>
                    <td>
                        <?php if ((int) $o['principal'] === 1): ?>
                            <span class="pill on">Principal</span>
                        <?php else: ?>
                            <form method="post" action="<?= site_url('casas/' . $casa['id'] . '/propietarios/' . $o['id'] . '/principal') ?>" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn secondary small">Hacer principal</button>
                            </form>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right;">
                        <form method="post" action="<?= site_url('casas/' . $casa['id'] . '/propietarios/' . $o['id'] . '/eliminar') ?>"
                              style="display:inline;" onsubmit="return confirm('¿Quitar a este propietario?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn danger small">Quitar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<form class="card" method="post" action="<?= site_url('casas/' . $casa['id'] . '/propietarios') ?>">
    <?= csrf_field() ?>
    <fieldset>
        <legend>Agregar propietario</legend>
        <?php if ($personas === []): ?>
            <p class="muted">No hay personas registradas en este condominio.
                <a href="<?= site_url('personas/nueva') ?>">Crea una persona</a> primero.</p>
        <?php else: ?>
            <div class="grid2">
                <div class="field">
                    <label>Persona *</label>
                    <select name="persona_id" required>
                        <option value="">— Selecciona —</option>
                        <?php foreach ($personas as $p): ?>
                            <option value="<?= (int) $p['id'] ?>"><?= esc(PersonaModel::fullName($p)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Porcentaje de copropiedad</label>
                    <input type="text" name="porcentaje" value="<?= esc(old('porcentaje', '100')) ?>">
                </div>
            </div>
            <div class="grid2">
                <div class="field">
                    <label>Fecha inicio</label>
                    <input type="text" name="fecha_inicio" value="<?= esc(old('fecha_inicio')) ?>" placeholder="AAAA-MM-DD">
                </div>
                <div class="field">
                    <label>Fecha fin</label>
                    <input type="text" name="fecha_fin" value="<?= esc(old('fecha_fin')) ?>" placeholder="AAAA-MM-DD">
                </div>
            </div>
            <div class="field">
                <label style="font-weight:400; display:flex; align-items:center; gap:.5rem;">
                    <input type="checkbox" name="principal" value="1" style="width:auto;">
                    Marcar como propietario principal
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">Agregar propietario</button>
            </div>
        <?php endif; ?>
    </fieldset>
</form>

<?= $this->endSection() ?>
