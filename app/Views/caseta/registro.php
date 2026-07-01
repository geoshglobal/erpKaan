<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <h1>Registrar paquetería / entrega</h1>
    <a class="btn secondary" href="<?= site_url('accesos') ?>">← Accesos</a>
</div>

<?php if (session('errors')): ?>
    <div class="alert error"><ul style="margin:0; padding-left:1.1rem;">
        <?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul></div>
<?php endif; ?>

<form class="card" method="post" action="<?= site_url('caseta/registro') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="field">
        <label>Tipo</label>
        <div class="segmented" id="tipo-seg">
            <label><input type="radio" name="tipo" value="paqueteria" checked> 📦 Paquetería</label>
            <label><input type="radio" name="tipo" value="delivery"> 🛵 Delivery</label>
            <label><input type="radio" name="tipo" value="proveedor"> 🔧 Proveedor</label>
        </div>
        <div class="muted" id="tipo-hint" style="font-size:.8rem; margin-top:.35rem;">
            El paquete queda en caseta hasta que el residente lo recoja.
        </div>
    </div>

    <div class="grid2">
        <div class="field">
            <label>Casa destino</label>
            <select name="casa_id" id="casa-select" required>
                <option value="">Selecciona…</option>
                <?php foreach ($casas as $c): ?>
                    <option value="<?= (int) $c['id'] ?>"><?= esc($c['identificador']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Destinatario (residente)</label>
            <select name="destinatario_persona_id" id="dest-select">
                <option value="">— Automático (principal) —</option>
            </select>
            <div class="muted" style="font-size:.78rem; margin-top:.25rem;">Se le enviará la notificación.</div>
        </div>
    </div>

    <div class="grid2">
        <div class="field">
            <label id="lbl-nombre">Repartidor / descripción</label>
            <input type="text" name="nombre_visitante" placeholder="Ej. Amazon, DHL, Uber Eats…" required>
        </div>
        <div class="field">
            <label>Empresa (opcional)</label>
            <input type="text" name="empresa" placeholder="Ej. Mercado Libre">
        </div>
    </div>

    <div class="field">
        <label>Notas (opcional)</label>
        <input type="text" name="notas" placeholder="Ej. caja grande, frágil">
    </div>

    <div class="field">
        <label>Foto (paquete o repartidor)</label>
        <input type="file" name="foto" accept="image/*" capture="environment">
    </div>

    <div class="form-actions">
        <button type="submit" class="btn">Registrar</button>
        <a class="btn secondary" href="<?= site_url('accesos') ?>">Cancelar</a>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    var DEST = <?= json_encode($destinatarios, JSON_UNESCAPED_UNICODE) ?>;
    var casaSel = document.getElementById('casa-select');
    var destSel = document.getElementById('dest-select');
    var hint = document.getElementById('tipo-hint');
    var lblNombre = document.getElementById('lbl-nombre');

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

    document.getElementById('tipo-seg').addEventListener('change', function (e) {
        var t = e.target.value;
        if (t === 'paqueteria') {
            hint.textContent = 'El paquete queda en caseta hasta que el residente lo recoja.';
            lblNombre.textContent = 'Repartidor / descripción';
        } else {
            hint.textContent = 'Ingreso directo: entra ahora y se registra su salida después.';
            lblNombre.textContent = (t === 'proveedor' ? 'Proveedor / servicio' : 'Repartidor / descripción');
        }
    });
})();
</script>
<?= $this->endSection() ?>
