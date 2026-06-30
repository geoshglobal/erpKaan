<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <h1>Escanear pase</h1>
    <a class="btn secondary" href="<?= site_url('accesos') ?>">← Accesos</a>
</div>

<div class="card" style="max-width:420px; margin:0 auto; text-align:center;">
    <p class="muted" style="margin-top:0;">Apunta la cámara al QR del visitante.</p>
    <div id="reader" style="width:100%;"></div>
    <p id="scan-status" class="muted" style="font-size:.85rem; margin-bottom:0;"></p>
    <button type="button" class="btn secondary small" id="btn-stop" style="display:none; margin-top:.5rem;">Detener</button>
</div>

<p class="muted" style="text-align:center; font-size:.82rem; margin-top:1rem;">
    También puedes buscar el acceso manualmente en <a href="<?= site_url('accesos') ?>">la lista de accesos</a>.
</p>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {
    var base   = <?= json_encode(site_url('pase/')) ?>;
    var status = document.getElementById('scan-status');
    var stopBtn = document.getElementById('btn-stop');
    if (typeof Html5Qrcode === 'undefined') { status.textContent = 'No se pudo cargar el lector.'; return; }

    var scanner = new Html5Qrcode('reader');
    var done = false;

    function onScan(text) {
        if (done) { return; }
        // Only follow links that point at our own pass route (avoid open redirects).
        if (text.indexOf(base) !== 0) {
            status.textContent = 'QR no reconocido como pase de acceso.';
            return;
        }
        done = true;
        status.textContent = 'Pase detectado, abriendo…';
        scanner.stop().then(function () { window.location.href = text; });
    }

    scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: 240 }, onScan)
        .then(function () { stopBtn.style.display = 'inline-block'; })
        .catch(function (e) { status.textContent = 'No se pudo abrir la cámara: ' + e; });

    stopBtn.addEventListener('click', function () {
        scanner.stop().then(function () { stopBtn.style.display = 'none'; status.textContent = 'Cámara detenida.'; });
    });
})();
</script>
<?= $this->endSection() ?>
