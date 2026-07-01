<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <h1>Pase de visita</h1>
    <a class="btn secondary" href="<?= site_url('portal/visitas') ?>">← Mis visitas</a>
</div>


<?= $this->include('partials/pase_card', ['acceso' => $acceso, 'casaIdent' => $casaIdent, 'passUrl' => $passUrl]) ?>

<?php if (! empty($acceso['check_in_at'])): ?>
    <?php $az = ['pendiente' => 'autorización pendiente', 'autorizado' => 'tu cajón autorizado', 'rechazado' => 'rechazado']; ?>
    <div class="card" style="max-width:420px; margin:1rem auto 0;">
        <h3 style="margin:0 0 .7rem; font-size:1rem;">Detalles del ingreso</h3>
        <p style="margin:.3rem 0;"><span class="muted">🕒 Entró:</span> <strong><?= esc(date('d/m/Y H:i', strtotime($acceso['check_in_at']))) ?></strong></p>
        <?php if (! empty($acceso['check_out_at'])): ?>
            <p style="margin:.3rem 0;"><span class="muted">🚪 Salió:</span> <strong><?= esc(date('d/m/Y H:i', strtotime($acceso['check_out_at']))) ?></strong></p>
        <?php endif; ?>
        <?php if ($acceso['pax_ingresaron'] !== null): ?>
            <p style="margin:.3rem 0;"><span class="muted">👥 Personas que ingresaron:</span> <strong><?= (int) $acceso['pax_ingresaron'] ?></strong>
                <?= (int) $acceso['pax_ingresaron'] > (int) $acceso['num_personas'] ? ' <span style="color:#b91c1c;">(más de las registradas: ' . (int) $acceso['num_personas'] . ')</span>' : '' ?></p>
        <?php endif; ?>
        <p style="margin:.3rem 0;"><span class="muted">🚗 Vehículo:</span>
            <?php if (! empty($acceso['ingreso_vehiculo'])): ?>
                <strong>Sí</strong><?= $acceso['placas'] ? ' · placas ' . esc($acceso['placas']) : '' ?><?= $acceso['folio_corbatin'] ? ' · folio ' . esc($acceso['folio_corbatin']) : '' ?>
            <?php else: ?>
                No
            <?php endif; ?>
        </p>
        <?php if (! empty($acceso['autorizacion_cajon'])): ?>
            <p style="margin:.3rem 0;"><span class="muted">🅿️ Cajón:</span> <?= esc($az[$acceso['autorizacion_cajon']] ?? $acceso['autorizacion_cajon']) ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div style="max-width:420px; margin:1rem auto 0; display:flex; gap:.5rem;">
    <input type="text" readonly value="<?= esc($passUrl) ?>" id="pass-link" onclick="this.select()"
           style="flex:1; padding:.5rem .6rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.82rem; background:#fff;">
    <button type="button" class="btn small" id="copy-pass"
            onclick="navigator.clipboard.writeText(document.getElementById('pass-link').value).then(()=>{var b=document.getElementById('copy-pass');b.textContent='¡Copiado!';setTimeout(()=>b.textContent='Copiar',1500);})">Copiar</button>
</div>
<p class="muted" style="text-align:center; font-size:.82rem; margin-top:.75rem;">
    Comparte el QR o el enlace con tu visitante. La caseta lo escaneará al llegar.
</p>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.js"></script>
<script>
(function () {
    var el = document.getElementById('qrcode');
    if (! el || typeof qrcode === 'undefined') { return; }
    var qr = qrcode(0, 'M');
    qr.addData(el.dataset.url);
    qr.make();
    el.innerHTML = qr.createImgTag(5, 8);
})();
</script>
<?= $this->endSection() ?>
