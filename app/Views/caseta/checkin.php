<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
use App\Libraries\Phone;

$tel = $solicitante['telefono'] ?? null;
$wa  = Phone::whatsapp($tel);
$cal = Phone::tel($tel);
$reg = (int) $acceso['num_personas'];
?>

<div class="page-head">
    <h1>Registrar entrada</h1>
    <a class="btn secondary" href="<?= site_url('accesos/' . $acceso['id']) ?>">← Acceso</a>
</div>

<?php if (session('errors')): ?>
    <div class="alert error"><ul style="margin:0; padding-left:1.1rem;">
        <?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul></div>
<?php endif; ?>

<div class="card" style="margin-bottom:1rem;">
    <strong><?= esc($acceso['nombre_visitante']) ?></strong>
    <span class="muted">— registrado para <?= $reg ?> persona<?= $reg === 1 ? '' : 's' ?></span>
</div>

<form class="card" method="post" action="<?= site_url('caseta/accesos/' . $acceso['id'] . '/checkin') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <fieldset>
        <legend>Ingreso</legend>
        <div class="field" style="max-width:50%;">
            <label>Personas que ingresan</label>
            <input type="number" min="1" name="pax_ingresaron" id="pax" value="<?= $reg ?>" data-reg="<?= $reg ?>">
        </div>
        <div id="pax-alert" class="alert error" style="display:none;">
            ⚠️ Ingresan <strong>más personas</strong> de las registradas (<?= $reg ?>). Confirma con el residente:
            <div style="margin-top:.5rem; display:flex; gap:.5rem; flex-wrap:wrap;">
                <?php if ($cal): ?><a class="btn small" href="<?= esc($cal) ?>">📞 Llamar</a><?php endif; ?>
                <?php if ($wa): ?><a class="btn small" style="background:#25d366;" href="<?= esc($wa) ?>" target="_blank" rel="noopener">🟢 WhatsApp</a><?php endif; ?>
                <?php if (! $cal && ! $wa): ?><span class="muted" style="font-size:.85rem;">El residente no tiene teléfono registrado.</span><?php endif; ?>
            </div>
        </div>

        <div class="field">
            <label style="display:flex; gap:.5rem; align-items:center; font-weight:400;">
                <input type="checkbox" name="ingreso_vehiculo" id="veh-check" value="1" style="width:auto;">
                Ingresa en vehículo
            </label>
        </div>
        <div id="veh" style="display:none;">
            <div class="grid2">
                <div class="field"><label>Folio de corbatín / cono</label>
                    <input type="text" name="folio_corbatin" placeholder="Ej. V-045"></div>
                <div class="field"><label>Placas</label>
                    <input type="text" name="placas" value="<?= esc($acceso['placas'] ?? '') ?>"></div>
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>Identificación</legend>
        <div class="field">
            <label>Foto de la ID (o del visitante)</label>
            <input type="file" name="id_foto" accept="image/*" capture="environment">
            <div class="muted" style="font-size:.78rem; margin-top:.25rem;">Puedes tomar la foto con la cámara.</div>
        </div>
        <div class="field">
            <label style="display:flex; gap:.5rem; align-items:center; font-weight:400;">
                <input type="checkbox" name="sin_id" id="noid-check" value="1" style="width:auto;">
                No proporcionó identificación
            </label>
        </div>
        <div class="field" id="noid" style="display:none;">
            <label>Motivo / autorización</label>
            <input type="text" name="id_nota" placeholder="Ej. autorizado por el residente, sin ID a la mano">
        </div>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn">Registrar entrada</button>
        <a class="btn secondary" href="<?= site_url('accesos/' . $acceso['id']) ?>">Cancelar</a>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    var pax = document.getElementById('pax');
    var reg = parseInt(pax.dataset.reg || '1', 10);
    function checkPax() {
        document.getElementById('pax-alert').style.display = (parseInt(pax.value || '0', 10) > reg) ? 'block' : 'none';
    }
    pax.addEventListener('input', checkPax); checkPax();

    document.getElementById('veh-check').addEventListener('change', function () {
        document.getElementById('veh').style.display = this.checked ? 'block' : 'none';
    });
    document.getElementById('noid-check').addEventListener('change', function () {
        document.getElementById('noid').style.display = this.checked ? 'block' : 'none';
    });
})();
</script>
<?= $this->endSection() ?>
