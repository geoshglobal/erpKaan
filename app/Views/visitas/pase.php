<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <h1>Pase de visita</h1>
    <a class="btn secondary" href="<?= site_url('portal/visitas') ?>">← Mis visitas</a>
</div>


<?= $this->include('partials/pase_card', ['acceso' => $acceso, 'casaIdent' => $casaIdent, 'passUrl' => $passUrl]) ?>

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
