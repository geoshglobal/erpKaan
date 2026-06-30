<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div style="margin:1.5rem auto 0; max-width:440px;">
    <h1 style="text-align:center; margin-bottom:1rem;">Pase de acceso</h1>
    <?= $this->include('partials/pase_card', ['acceso' => $acceso, 'casaIdent' => $casaIdent, 'passUrl' => $passUrl]) ?>
    <p class="muted" style="text-align:center; font-size:.82rem; margin-top:1rem;">
        Presenta este código en la caseta de acceso.
    </p>
</div>

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
