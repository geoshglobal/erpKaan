<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php use App\Libraries\Horario; ?>

<div class="page-head">
    <h1>Avisar delivery o proveedor</h1>
    <a class="btn secondary" href="<?= site_url('portal/paquetes') ?>">← Paquetería</a>
</div>

<?php if (session('errors')): ?>
    <div class="alert error"><ul style="margin:0; padding-left:1.1rem;">
        <?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul></div>
<?php endif; ?>

<form class="card" method="post" action="<?= site_url('portal/avisos') ?>">
    <?= csrf_field() ?>

    <div class="field">
        <label>Tipo</label>
        <div class="segmented" id="tipo-seg">
            <label><input type="radio" name="tipo" value="delivery" <?= old('tipo', 'delivery') === 'delivery' ? 'checked' : '' ?>> 🛵 Delivery</label>
            <label><input type="radio" name="tipo" value="proveedor" <?= old('tipo') === 'proveedor' ? 'checked' : '' ?>> 🔧 Proveedor</label>
        </div>
        <div class="alert" id="horario-info" style="margin-top:.6rem; background:#f0f9ff; color:#075985; border:1px solid #bae6fd; font-size:.85rem;"></div>
    </div>

    <div class="grid2">
        <div class="field">
            <label>Casa</label>
            <select name="casa_id" required>
                <?php foreach ($casas as $id => $ident): ?>
                    <option value="<?= (int) $id ?>" <?= (int) old('casa_id') === (int) $id ? 'selected' : '' ?>><?= esc($ident) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label id="lbl-nombre">Repartidor / empresa</label>
            <input type="text" name="nombre_visitante" value="<?= esc(old('nombre_visitante')) ?>" placeholder="Ej. Uber Eats, DiDi Food…" required>
        </div>
    </div>

    <div class="field">
        <label>Empresa / detalle (opcional)</label>
        <input type="text" name="empresa" value="<?= esc(old('empresa')) ?>" placeholder="Ej. plomería, pedido #1234">
    </div>

    <div id="veh-section" style="display:<?= old('tipo') === 'proveedor' ? 'block' : 'none' ?>;">
        <div class="field">
            <label style="display:flex; gap:.5rem; align-items:center; font-weight:400;">
                <input type="checkbox" name="permite_vehiculo" id="veh-perm" value="1" <?= old('permite_vehiculo') ? 'checked' : '' ?> style="width:auto;">
                Ingresa en vehículo
            </label>
        </div>
        <div class="field" id="cajon-propio" style="display:<?= old('permite_vehiculo') ? 'block' : 'none' ?>; margin-left:1.6rem;">
            <label style="display:flex; gap:.5rem; align-items:flex-start; font-weight:400;">
                <input type="checkbox" name="autoriza_cajon_propio" value="1" <?= old('autoriza_cajon_propio') ? 'checked' : '' ?> style="width:auto; margin-top:.25rem;">
                <span>Autorizo el uso de <strong>mi cajón</strong> si no hay lugar de visitas disponible</span>
            </label>
        </div>
    </div>

    <fieldset>
        <legend>¿Cuándo?</legend>
        <label style="display:flex; gap:.5rem; align-items:center; font-weight:400; margin-bottom:.6rem;">
            <input type="radio" name="vigencia" value="inmediata" checked style="width:auto;" onclick="document.getElementById('prog').style.display='none'">
            <span><strong>Ahora</strong> <span class="muted">— llega hoy</span></span>
        </label>
        <label style="display:flex; gap:.5rem; align-items:center; font-weight:400;">
            <input type="radio" name="vigencia" value="programada" style="width:auto;" onclick="document.getElementById('prog').style.display='grid'">
            <span><strong>Programar</strong> <span class="muted">— ventana de fecha/hora</span></span>
        </label>
        <div class="grid2" id="prog" style="display:none; margin-top:.75rem;">
            <div class="field"><label>Desde</label>
                <input type="datetime-local" name="valido_desde" value="<?= esc(old('valido_desde')) ?>"></div>
            <div class="field"><label>Hasta</label>
                <input type="datetime-local" name="valido_hasta" value="<?= esc(old('valido_hasta')) ?>"></div>
        </div>
    </fieldset>

    <div class="field"><label>Notas (opcional)</label>
        <input type="text" name="notas" value="<?= esc(old('notas')) ?>"></div>

    <div class="form-actions">
        <button type="submit" class="btn">Avisar a caseta</button>
        <a class="btn secondary" href="<?= site_url('portal/paquetes') ?>">Cancelar</a>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    var RES = {
        delivery: <?= json_encode(Horario::resumen($horarios['delivery']), JSON_UNESCAPED_UNICODE) ?>,
        proveedor: <?= json_encode(Horario::resumen($horarios['proveedor']), JSON_UNESCAPED_UNICODE) ?>
    };
    var seg = document.getElementById('tipo-seg');
    var info = document.getElementById('horario-info');
    var lbl = document.getElementById('lbl-nombre');
    var vehSection = document.getElementById('veh-section');
    function upd() {
        var t = (seg.querySelector('input:checked') || {}).value || 'delivery';
        info.textContent = '🕒 Horario permitido de ' + t + ': ' + (RES[t] || 'sin restricción');
        lbl.textContent = (t === 'proveedor' ? 'Proveedor / servicio' : 'Repartidor / empresa');
        // Vehicle/parking only applies to proveedores.
        if (vehSection) { vehSection.style.display = (t === 'proveedor') ? 'block' : 'none'; }
    }
    seg.addEventListener('change', upd); upd();

    var perm = document.getElementById('veh-perm');
    var box = document.getElementById('cajon-propio');
    if (perm && box) { perm.addEventListener('change', function () { box.style.display = this.checked ? 'block' : 'none'; }); }
})();
</script>
<?= $this->endSection() ?>
