<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$val = static fn (string $f, $default = '') => old($f, is_array($cajon ?? null) ? ($cajon[$f] ?? $default) : $default);
$tipos = ['asignado' => 'Asignado', 'visita' => 'Visita', 'comun' => 'Común'];
$selectedCasa = (int) $val('casa_id', 0);
$selectedTipo = (string) $val('tipo', 'asignado');
$techadoVal   = $val('techado', '');
?>

<?= $this->include('partials/propiedades_nav', ['current' => 'cajones']) ?>

<div class="page-head">
    <h1><?= esc($title) ?></h1>
    <a class="btn secondary" href="<?= site_url('cajones') ?>">← Volver</a>
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
            <label>Identificador * (ej. E-23)</label>
            <input type="text" name="identificador" value="<?= esc($val('identificador')) ?>" required>
        </div>
        <div class="field">
            <label>Tipo</label>
            <select name="tipo">
                <?php foreach ($tipos as $k => $label): ?>
                    <option value="<?= $k ?>" <?= $k === $selectedTipo ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="grid2">
        <div class="field">
            <label>Casa asignada</label>
            <select name="casa_id">
                <option value="">— Sin asignar (visita / común) —</option>
                <?php foreach ($casas as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (int) $c['id'] === $selectedCasa ? 'selected' : '' ?>>
                        <?= esc($c['identificador']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Techado</label>
            <select name="techado">
                <option value=""  <?= $techadoVal === '' || $techadoVal === null ? 'selected' : '' ?>>— Sin especificar —</option>
                <option value="1" <?= (string) $techadoVal === '1' ? 'selected' : '' ?>>Techado</option>
                <option value="0" <?= (string) $techadoVal === '0' ? 'selected' : '' ?>>Descubierto</option>
            </select>
        </div>
    </div>
    <div class="field" style="max-width:50%;">
        <label>Estado</label>
        <?php $activo = (int) $val('activo', 1); ?>
        <select name="activo">
            <option value="1" <?= $activo === 1 ? 'selected' : '' ?>>Activo</option>
            <option value="0" <?= $activo === 0 ? 'selected' : '' ?>>Inactivo</option>
        </select>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn">Guardar</button>
        <a class="btn secondary" href="<?= site_url('cajones') ?>">Cancelar</a>
    </div>
</form>

<?= $this->endSection() ?>
