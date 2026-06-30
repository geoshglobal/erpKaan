<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
$val = static fn (string $f, $default = '') => old($f, is_array($condominio ?? null) ? ($condominio[$f] ?? $default) : $default);
$activo = (int) $val('activo', 1);
?>

<div class="page-head">
    <h1><?= esc($title) ?></h1>
    <a class="btn secondary" href="<?= site_url('condominios') ?>">← Volver</a>
</div>

<?php if (session('errors')): ?>
    <div class="alert error">
        <strong>Revisa los campos:</strong>
        <ul style="margin:.4rem 0 0; padding-left:1.1rem;">
            <?php foreach ((array) session('errors') as $e): ?>
                <li><?= esc($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="card" method="post" action="<?= esc($action) ?>">
    <?= csrf_field() ?>

    <fieldset>
        <legend>General</legend>
        <div class="field">
            <label>Nombre *</label>
            <input type="text" name="nombre" value="<?= esc($val('nombre')) ?>" required>
        </div>
        <div class="grid2">
            <div class="field">
                <label>Slug (identificador URL)</label>
                <input type="text" name="slug" value="<?= esc($val('slug')) ?>" placeholder="Se genera del nombre si lo dejas vacío">
            </div>
            <div class="field">
                <label>Estado</label>
                <select name="activo">
                    <option value="1" <?= $activo === 1 ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= $activo === 0 ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
        </div>
        <div class="field">
            <label>Dirección</label>
            <input type="text" name="direccion" value="<?= esc($val('direccion')) ?>">
        </div>
        <div class="grid2">
            <div class="field">
                <label>Colonia</label>
                <input type="text" name="colonia" value="<?= esc($val('colonia')) ?>">
            </div>
            <div class="field">
                <label>Municipio / Alcaldía</label>
                <input type="text" name="municipio" value="<?= esc($val('municipio')) ?>">
            </div>
        </div>
        <div class="grid2">
            <div class="field">
                <label>Estado (entidad)</label>
                <input type="text" name="estado" value="<?= esc($val('estado')) ?>">
            </div>
            <div class="field">
                <label>Código postal</label>
                <input type="text" name="cp" value="<?= esc($val('cp')) ?>">
            </div>
        </div>
        <div class="grid2">
            <div class="field">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="<?= esc($val('telefono')) ?>">
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" value="<?= esc($val('email')) ?>">
            </div>
        </div>
        <div class="grid2">
            <div class="field">
                <label>País</label>
                <input type="text" name="pais" maxlength="2" value="<?= esc($val('pais', 'MX')) ?>">
            </div>
            <div class="field">
                <label>Moneda</label>
                <input type="text" name="moneda" maxlength="3" value="<?= esc($val('moneda', 'MXN')) ?>">
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>Datos fiscales (CFDI · emisor)</legend>
        <div class="field">
            <label>Razón social</label>
            <input type="text" name="razon_social" value="<?= esc($val('razon_social')) ?>">
        </div>
        <div class="grid2">
            <div class="field">
                <label>RFC</label>
                <input type="text" name="rfc" maxlength="13" value="<?= esc($val('rfc')) ?>">
            </div>
            <div class="field">
                <label>Régimen fiscal (clave SAT)</label>
                <input type="text" name="regimen_fiscal" value="<?= esc($val('regimen_fiscal')) ?>">
            </div>
        </div>
        <div class="field" style="max-width:50%;">
            <label>C.P. fiscal</label>
            <input type="text" name="cp_fiscal" value="<?= esc($val('cp_fiscal')) ?>">
        </div>
    </fieldset>

    <fieldset>
        <legend>Ubicación en el mapa</legend>
        <p class="muted" style="margin-top:0; font-size:.85rem;">
            Haz clic en el mapa o arrastra el marcador para fijar la ubicación. El botón centra
            el mapa según país, estado y código postal.
        </p>
        <button type="button" class="btn secondary small" id="btn-geocode" style="margin-bottom:.6rem;">
            📍 Centrar según CP
        </button>
        <span class="muted" id="geo-status" style="font-size:.8rem; margin-left:.5rem;"></span>

        <div id="map" style="height:340px; border:1px solid #cbd5e1; border-radius:10px;"></div>

        <input type="hidden" name="latitud"  id="latitud"  value="<?= esc($val('latitud')) ?>">
        <input type="hidden" name="longitud" id="longitud" value="<?= esc($val('longitud')) ?>">
        <p class="muted" style="font-size:.8rem; margin-bottom:0;">
            Coordenadas: <span id="coords"><?= $val('latitud') && $val('longitud') ? esc($val('latitud')) . ', ' . esc($val('longitud')) : '— sin definir —' ?></span>
        </p>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn">Guardar</button>
        <a class="btn secondary" href="<?= site_url('condominios') ?>">Cancelar</a>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    var latInput = document.getElementById('latitud');
    var lngInput = document.getElementById('longitud');
    var coords   = document.getElementById('coords');
    var status   = document.getElementById('geo-status');

    var hasInitial = latInput.value !== '' && lngInput.value !== '';
    var startLat   = hasInitial ? parseFloat(latInput.value) : 23.6345;   // centroide MX
    var startLng   = hasInitial ? parseFloat(lngInput.value) : -102.5528;
    var startZoom  = hasInitial ? 16 : 5;

    var map = L.map('map').setView([startLat, startLng], startZoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    var marker = null;
    function setMarker(lat, lng) {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', function (e) {
                var p = e.target.getLatLng();
                writeCoords(p.lat, p.lng);
            });
        }
        writeCoords(lat, lng);
    }
    function writeCoords(lat, lng) {
        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);
        coords.textContent = latInput.value + ', ' + lngInput.value;
    }

    if (hasInitial) {
        setMarker(startLat, startLng);
    }

    map.on('click', function (e) {
        setMarker(e.latlng.lat, e.latlng.lng);
    });

    // Geocode from país + estado + CP (+ municipio) via Nominatim (OSM).
    document.getElementById('btn-geocode').addEventListener('click', function () {
        var val = function (name) {
            var el = document.querySelector('[name="' + name + '"]');
            return el ? el.value.trim() : '';
        };
        var params = new URLSearchParams({ format: 'json', limit: '1', addressdetails: '0' });
        if (val('pais'))      params.set('countrycodes', val('pais').toLowerCase());
        if (val('estado'))    params.set('state', val('estado'));
        if (val('municipio')) params.set('county', val('municipio'));
        if (val('cp'))        params.set('postalcode', val('cp'));

        status.textContent = 'Buscando…';
        fetch('https://nominatim.openstreetmap.org/search?' + params.toString(), {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data && data.length) {
                var lat = parseFloat(data[0].lat), lng = parseFloat(data[0].lon);
                map.setView([lat, lng], 15);
                setMarker(lat, lng);
                status.textContent = 'Ubicación encontrada ✔ (ajusta el marcador si es necesario)';
            } else {
                status.textContent = 'No se encontró; ubica manualmente en el mapa.';
            }
        })
        .catch(function () { status.textContent = 'Error al geocodificar; ubica manualmente.'; });
    });
})();
</script>
<?= $this->endSection() ?>
