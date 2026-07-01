<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php $tipos = \App\Models\AccesoModel::TIPOS; ?>

<div class="page-head">
    <h1>Corregir casa del registro</h1>
    <a class="btn secondary" href="<?= site_url('accesos/' . $acceso['id']) ?>">← Acceso</a>
</div>

<?php if (session('error')): ?><div class="alert error"><?= esc(session('error')) ?></div><?php endif; ?>

<div class="card" style="margin-bottom:1rem;">
    <strong><?= esc($acceso['nombre_visitante']) ?></strong>
    <span class="muted">— <?= esc($tipos[$acceso['tipo']] ?? $acceso['tipo']) ?> · actualmente casa <?= esc($acceso['casa_id']) ?></span>
    <div class="muted" style="font-size:.83rem; margin-top:.35rem;">
        Al corregir la casa, se avisará al residente anterior que fue un error y se notificará al nuevo destinatario.
    </div>
</div>

<form class="card" method="post" action="<?= site_url('caseta/accesos/' . $acceso['id'] . '/reasignar') ?>">
    <?= csrf_field() ?>
    <div class="grid2">
        <div class="field">
            <label>Casa correcta</label>
            <select name="casa_id" id="casa-select" required>
                <option value="">Selecciona…</option>
                <?php foreach ($casas as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (int) $acceso['casa_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['identificador']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Destinatario</label>
            <select name="destinatario_persona_id" id="dest-select">
                <option value="">— Automático (principal) —</option>
            </select>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn">Reasignar y corregir avisos</button>
        <a class="btn secondary" href="<?= site_url('accesos/' . $acceso['id']) ?>">Cancelar</a>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    var DEST = <?= json_encode($destinatarios, JSON_UNESCAPED_UNICODE) ?>;
    var casaSel = document.getElementById('casa-select');
    var destSel = document.getElementById('dest-select');
    function fillDest() {
        var id = casaSel.value;
        destSel.innerHTML = '<option value="">— Automático (principal) —</option>';
        (DEST[id] || []).forEach(function (r) {
            var o = document.createElement('option');
            o.value = r.id;
            o.textContent = r.nombre + (r.principal ? ' (principal)' : '');
            destSel.appendChild(o);
        });
    }
    casaSel.addEventListener('change', fillDest);
    fillDest();
})();
</script>
<?= $this->endSection() ?>
