<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php $val = static fn (string $f, $default = '') => old($f, is_array($persona ?? null) ? ($persona[$f] ?? $default) : $default); ?>

<div class="page-head">
    <h1><?= esc($title) ?></h1>
    <a class="btn secondary" href="<?= site_url('personas') ?>">← Volver</a>
</div>

<?php if (session('errors')): ?>
    <div class="alert error">
        <ul style="margin:0; padding-left:1.1rem;">
            <?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="card" method="post" action="<?= esc($action) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <fieldset>
        <legend>Datos personales</legend>
        <div class="grid2">
            <div class="field">
                <label>Nombre(s) *</label>
                <input type="text" name="nombre" value="<?= esc($val('nombre')) ?>" required>
            </div>
            <div class="field">
                <label>Fecha de nacimiento</label>
                <input type="text" name="fecha_nacimiento" value="<?= esc($val('fecha_nacimiento')) ?>" placeholder="AAAA-MM-DD">
            </div>
        </div>
        <div class="grid2">
            <div class="field">
                <label>Apellido paterno</label>
                <input type="text" name="apellido_paterno" value="<?= esc($val('apellido_paterno')) ?>">
            </div>
            <div class="field">
                <label>Apellido materno</label>
                <input type="text" name="apellido_materno" value="<?= esc($val('apellido_materno')) ?>">
            </div>
        </div>

        <div class="field">
            <label>Foto</label>
            <?php if (! empty($persona['foto_path'])): ?>
                <div style="margin-bottom:.5rem;">
                    <img src="<?= base_url(esc($persona['foto_path'])) ?>" alt="Foto actual"
                         style="width:72px; height:72px; border-radius:8px; object-fit:cover; border:1px solid #cbd5e1;">
                    <span class="muted" style="font-size:.8rem; margin-left:.5rem;">Foto actual (sube una nueva para reemplazarla)</span>
                </div>
            <?php endif; ?>
            <input type="file" name="foto" accept="image/png,image/jpeg,image/webp">
            <div class="muted" style="font-size:.78rem; margin-top:.25rem;">JPG, PNG o WebP · máx. 3 MB</div>
        </div>
    </fieldset>

    <fieldset>
        <legend>Contacto</legend>
        <div class="grid2">
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" value="<?= esc($val('email')) ?>">
            </div>
            <div class="field">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="<?= esc($val('telefono')) ?>">
            </div>
        </div>
        <div class="grid2">
            <div class="field">
                <label>Teléfono alterno</label>
                <input type="text" name="telefono2" value="<?= esc($val('telefono2')) ?>">
            </div>
            <div class="field">
                <label>Estado</label>
                <?php $activo = (int) $val('activo', 1); ?>
                <select name="activo">
                    <option value="1" <?= $activo === 1 ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= $activo === 0 ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>Datos fiscales (CFDI · receptor)</legend>
        <div class="grid2">
            <div class="field">
                <label>RFC</label>
                <input type="text" name="rfc" maxlength="13" value="<?= esc($val('rfc')) ?>">
            </div>
            <div class="field">
                <label>Razón social</label>
                <input type="text" name="razon_social" value="<?= esc($val('razon_social')) ?>">
            </div>
        </div>
        <div class="grid2">
            <div class="field">
                <label>Régimen fiscal (clave SAT)</label>
                <input type="text" name="regimen_fiscal" value="<?= esc($val('regimen_fiscal')) ?>">
            </div>
            <div class="field">
                <label>Uso CFDI</label>
                <input type="text" name="uso_cfdi" value="<?= esc($val('uso_cfdi')) ?>">
            </div>
        </div>
        <div class="field" style="max-width:50%;">
            <label>C.P. fiscal</label>
            <input type="text" name="cp_fiscal" value="<?= esc($val('cp_fiscal')) ?>">
        </div>
    </fieldset>

    <div class="field">
        <label>Notas</label>
        <input type="text" name="notas" value="<?= esc($val('notas')) ?>">
    </div>

    <div class="form-actions">
        <button type="submit" class="btn">Guardar</button>
        <a class="btn secondary" href="<?= site_url('personas') ?>">Cancelar</a>
    </div>
</form>

<?php if (isset($propiedades)): ?>
    <div class="card" style="margin-top:1.5rem;">
        <h2 style="margin:0 0 .75rem; font-size:1.05rem;">Propiedades de esta persona</h2>
        <?php if ($propiedades === []): ?>
            <p class="muted" style="margin:0;">No es propietaria de ninguna casa todavía. Las asignaciones se hacen
                desde <a href="<?= site_url('casas') ?>">Casas → Dueños</a>.</p>
        <?php else: ?>
            <table class="grid">
                <thead><tr><th>Casa</th><th>%</th><th>Principal</th><th style="text-align:right;"></th></tr></thead>
                <tbody>
                    <?php foreach ($propiedades as $pr): ?>
                        <tr>
                            <td><strong><?= esc($pr['identificador']) ?></strong></td>
                            <td><?= esc(rtrim(rtrim((string) $pr['porcentaje'], '0'), '.')) ?>%</td>
                            <td><?= (int) $pr['principal'] === 1 ? '<span class="pill on">Principal</span>' : '<span class="muted">—</span>' ?></td>
                            <td style="text-align:right;">
                                <a class="btn secondary small" href="<?= site_url('casas/' . $pr['casa_id'] . '/propietarios') ?>">Ver casa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
