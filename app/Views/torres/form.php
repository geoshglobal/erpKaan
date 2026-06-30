<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php $val = static fn (string $f, $default = '') => old($f, is_array($torre ?? null) ? ($torre[$f] ?? $default) : $default); ?>

<?= $this->include('partials/propiedades_nav', ['current' => 'torres']) ?>

<div class="page-head">
    <h1><?= esc($title) ?></h1>
    <a class="btn secondary" href="<?= site_url('torres') ?>">← Volver</a>
</div>

<?php if (session('errors')): ?>
    <div class="alert error">
        <ul style="margin:0; padding-left:1.1rem;">
            <?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="card" method="post" action="<?= esc($action) ?>">
    <?= csrf_field() ?>
    <div class="grid2">
        <div class="field">
            <label>Nombre *</label>
            <input type="text" name="nombre" value="<?= esc($val('nombre')) ?>" required>
        </div>
        <div class="field">
            <label>Clave (ej. A)</label>
            <input type="text" name="clave" value="<?= esc($val('clave')) ?>">
        </div>
    </div>
    <div class="field">
        <label>Descripción</label>
        <input type="text" name="descripcion" value="<?= esc($val('descripcion')) ?>">
    </div>
    <div class="grid2">
        <div class="field">
            <label>Orden</label>
            <input type="text" name="orden" value="<?= esc($val('orden', '0')) ?>">
        </div>
        <div class="field">
            <label>Estado</label>
            <?php $activo = (int) $val('activo', 1); ?>
            <select name="activo">
                <option value="1" <?= $activo === 1 ? 'selected' : '' ?>>Activa</option>
                <option value="0" <?= $activo === 0 ? 'selected' : '' ?>>Inactiva</option>
            </select>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn">Guardar</button>
        <a class="btn secondary" href="<?= site_url('torres') ?>">Cancelar</a>
    </div>
</form>

<?= $this->endSection() ?>
