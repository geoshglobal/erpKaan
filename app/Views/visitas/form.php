<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <h1>Nueva visita</h1>
    <a class="btn secondary" href="<?= site_url('portal/visitas') ?>">← Mis visitas</a>
</div>

<?php if (session('errors')): ?>
    <div class="alert error"><ul style="margin:0; padding-left:1.1rem;">
        <?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul></div>
<?php endif; ?>

<form class="card" method="post" action="<?= site_url('portal/visitas') ?>">
    <?= csrf_field() ?>

    <fieldset>
        <legend>Visitante</legend>
        <div class="grid2">
            <div class="field"><label>Nombre del visitante *</label>
                <input type="text" name="nombre_visitante" value="<?= esc(old('nombre_visitante')) ?>" required></div>
            <div class="field"><label>Casa *</label>
                <select name="casa_id" required>
                    <?php foreach ($casas as $id => $ident): ?>
                        <option value="<?= (int) $id ?>" <?= (string) old('casa_id') === (string) $id ? 'selected' : '' ?>><?= esc($ident) ?></option>
                    <?php endforeach; ?>
                </select></div>
        </div>
        <div class="grid2">
            <div class="field"><label>Empresa / motivo</label>
                <input type="text" name="empresa" value="<?= esc(old('empresa')) ?>" placeholder="Opcional"></div>
            <div class="field"><label>Teléfono</label>
                <input type="text" name="telefono" value="<?= esc(old('telefono')) ?>"></div>
        </div>
        <div class="grid2">
            <div class="field"><label>Número de personas</label>
                <input type="text" name="num_personas" value="<?= esc(old('num_personas', '1')) ?>"></div>
            <div class="field"><label>Placas del vehículo</label>
                <input type="text" name="placas" value="<?= esc(old('placas')) ?>"></div>
        </div>
        <div class="field">
            <label style="display:flex; gap:.5rem; align-items:center; font-weight:400;">
                <input type="checkbox" name="permite_vehiculo" value="1" <?= old('permite_vehiculo') ? 'checked' : '' ?> style="width:auto;">
                Permitir acceso en vehículo
            </label>
        </div>
    </fieldset>

    <fieldset>
        <legend>Vigencia</legend>
        <label style="display:flex; gap:.5rem; align-items:center; font-weight:400; margin-bottom:.6rem;">
            <input type="radio" name="vigencia" value="inmediata" checked style="width:auto;" onclick="document.getElementById('prog').style.display='none'">
            <span><strong>Inmediata</strong> <span class="muted">— válida hoy hasta el final del día</span></span>
        </label>
        <label style="display:flex; gap:.5rem; align-items:center; font-weight:400;">
            <input type="radio" name="vigencia" value="programada" style="width:auto;" onclick="document.getElementById('prog').style.display='grid'">
            <span><strong>Programada</strong> <span class="muted">— define una ventana de fecha/hora</span></span>
        </label>
        <div class="grid2" id="prog" style="display:none; margin-top:.75rem;">
            <div class="field"><label>Desde</label>
                <input type="datetime-local" name="valido_desde" value="<?= esc(old('valido_desde')) ?>"></div>
            <div class="field"><label>Hasta</label>
                <input type="datetime-local" name="valido_hasta" value="<?= esc(old('valido_hasta')) ?>"></div>
        </div>
    </fieldset>

    <div class="field"><label>Notas</label>
        <input type="text" name="notas" value="<?= esc(old('notas')) ?>"></div>

    <div class="form-actions">
        <button type="submit" class="btn">Crear visita y generar QR</button>
        <a class="btn secondary" href="<?= site_url('portal/visitas') ?>">Cancelar</a>
    </div>
</form>

<?= $this->endSection() ?>
