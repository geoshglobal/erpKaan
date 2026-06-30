<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$val = static fn (string $f, $default = '') => old($f, is_array($casa ?? null) ? ($casa[$f] ?? $default) : $default);
$usos = ['propio' => 'Uso propio', 'renta_lineal' => 'Renta lineal', 'renta_vacacional' => 'Renta vacacional'];
$selectedTorre = (int) $val('torre_id', 0);
$selectedUso   = (string) $val('tipo_ocupacion_actual', 'propio');
?>

<?= $this->include('partials/propiedades_nav', ['current' => 'casas']) ?>

<div class="page-head">
    <h1><?= esc($title) ?></h1>
    <a class="btn secondary" href="<?= site_url('casas') ?>">← Volver</a>
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
            <label>Identificador * (ej. A-101)</label>
            <input type="text" name="identificador" value="<?= esc($val('identificador')) ?>" required>
        </div>
        <div class="field">
            <label>Torre</label>
            <select name="torre_id">
                <option value="">— Sin torre —</option>
                <?php foreach ($torres as $t): ?>
                    <option value="<?= (int) $t['id'] ?>" <?= (int) $t['id'] === $selectedTorre ? 'selected' : '' ?>>
                        <?= esc($t['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="grid2">
        <div class="field">
            <label>Uso actual</label>
            <select name="tipo_ocupacion_actual">
                <?php foreach ($usos as $k => $label): ?>
                    <option value="<?= $k ?>" <?= $k === $selectedUso ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
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
    <div class="grid2">
        <div class="field">
            <label>Número de cajones</label>
            <input type="text" name="num_cajones" value="<?= esc($val('num_cajones', '0')) ?>">
        </div>
        <div class="field">
            <label>Metros cuadrados (m²)</label>
            <input type="text" name="m2" value="<?= esc($val('m2')) ?>">
        </div>
    </div>
    <div class="field" style="max-width:50%;">
        <label>Máx. ocupantes (override del condominio)</label>
        <input type="text" name="max_ocupantes" value="<?= esc($val('max_ocupantes')) ?>" placeholder="Vacío = usa el default del condominio">
    </div>
    <div class="field">
        <label>Notas</label>
        <input type="text" name="notas" value="<?= esc($val('notas')) ?>">
    </div>
    <div class="form-actions">
        <button type="submit" class="btn">Guardar</button>
        <a class="btn secondary" href="<?= site_url('casas') ?>">Cancelar</a>
    </div>
</form>

<?= $this->endSection() ?>
