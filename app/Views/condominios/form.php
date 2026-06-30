<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$val = static fn (string $f, $default = '') => old($f, is_array($condominio ?? null) ? ($condominio[$f] ?? $default) : $default);
$activo = (int) $val('activo', 1);
?>

<div class="page-head">
    <h1><?= esc($title) ?></h1>
    <a class="btn secondary" href="<?= site_url('condominios') ?>">← Volver</a>
</div>

<?php if (session('errors')): ?>
    <div class="alert error">
        <strong>Revisa los campos:</strong>
        <ul style="margin:.4rem 0 0; padding-left:1.1rem;">
            <?php foreach ((array) session('errors') as $e): ?>
                <li><?= esc($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="card" method="post" action="<?= esc($action) ?>">
    <?= csrf_field() ?>

    <fieldset>
        <legend>General</legend>
        <div class="field">
            <label>Nombre *</label>
            <input type="text" name="nombre" value="<?= esc($val('nombre')) ?>" required>
        </div>
        <div class="grid2">
            <div class="field">
                <label>Slug (identificador URL)</label>
                <input type="text" name="slug" value="<?= esc($val('slug')) ?>" placeholder="Se genera del nombre si lo dejas vacío">
            </div>
            <div class="field">
                <label>Estado</label>
                <select name="activo">
                    <option value="1" <?= $activo === 1 ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= $activo === 0 ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
        </div>
        <div class="field">
            <label>Dirección</label>
            <input type="text" name="direccion" value="<?= esc($val('direccion')) ?>">
        </div>
        <div class="grid2">
            <div class="field">
                <label>Colonia</label>
                <input type="text" name="colonia" value="<?= esc($val('colonia')) ?>">
            </div>
            <div class="field">
                <label>Municipio / Alcaldía</label>
                <input type="text" name="municipio" value="<?= esc($val('municipio')) ?>">
            </div>
        </div>
        <div class="grid2">
            <div class="field">
                <label>Estado (entidad)</label>
                <input type="text" name="estado" value="<?= esc($val('estado')) ?>">
            </div>
            <div class="field">
                <label>Código postal</label>
                <input type="text" name="cp" value="<?= esc($val('cp')) ?>">
            </div>
        </div>
        <div class="grid2">
            <div class="field">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="<?= esc($val('telefono')) ?>">
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" value="<?= esc($val('email')) ?>">
            </div>
        </div>
        <div class="grid2">
            <div class="field">
                <label>País</label>
                <input type="text" name="pais" maxlength="2" value="<?= esc($val('pais', 'MX')) ?>">
            </div>
            <div class="field">
                <label>Moneda</label>
                <input type="text" name="moneda" maxlength="3" value="<?= esc($val('moneda', 'MXN')) ?>">
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>Datos fiscales (CFDI · emisor)</legend>
        <div class="field">
            <label>Razón social</label>
            <input type="text" name="razon_social" value="<?= esc($val('razon_social')) ?>">
        </div>
        <div class="grid2">
            <div class="field">
                <label>RFC</label>
                <input type="text" name="rfc" maxlength="13" value="<?= esc($val('rfc')) ?>">
            </div>
            <div class="field">
                <label>Régimen fiscal (clave SAT)</label>
                <input type="text" name="regimen_fiscal" value="<?= esc($val('regimen_fiscal')) ?>">
            </div>
        </div>
        <div class="field" style="max-width:50%;">
            <label>C.P. fiscal</label>
            <input type="text" name="cp_fiscal" value="<?= esc($val('cp_fiscal')) ?>">
        </div>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn">Guardar</button>
        <a class="btn secondary" href="<?= site_url('condominios') ?>">Cancelar</a>
    </div>
</form>

<?= $this->endSection() ?>
