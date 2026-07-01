<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="page-head">
    <h1>Configuración de notificaciones</h1>
    <a class="btn secondary" href="<?= site_url('notificaciones') ?>">← Notificaciones</a>
</div>

<form class="card" method="post" action="<?= site_url('notificaciones/preferencias') ?>" style="max-width:560px;">
    <?= csrf_field() ?>

    <fieldset>
        <legend>Canales</legend>

        <label style="display:flex; gap:.7rem; align-items:flex-start; padding:.6rem 0; border-bottom:1px solid #e2e8f0;">
            <input type="checkbox" name="email" value="1" style="width:auto; margin-top:.2rem;" <?= $prefs['email'] ? 'checked' : '' ?>>
            <span>
                <strong>📧 Correo electrónico</strong>
                <div class="muted" style="font-size:.85rem;">
                    Recibe un correo cuando haya novedades de tus visitas.
                    <?php if ($emailAddr): ?><br>Se enviará a <strong><?= esc($emailAddr) ?></strong>.<?php endif; ?>
                    <?php if (! $mailGlobal): ?><br><span style="color:#b45309;">⚠️ El envío de correo está desactivado a nivel plataforma por ahora.</span><?php endif; ?>
                </div>
            </span>
        </label>

        <label style="display:flex; gap:.7rem; align-items:flex-start; padding:.6rem 0;">
            <input type="checkbox" name="push" value="1" style="width:auto; margin-top:.2rem;" <?= $prefs['push'] ? 'checked' : '' ?>>
            <span>
                <strong>🔔 Notificaciones push (navegador)</strong>
                <div class="muted" style="font-size:.85rem;">
                    Avisos instantáneos en este dispositivo, incluso con Kaan cerrado.
                    Debes además <strong>activar el permiso</strong> en cada navegador/dispositivo con el botón de abajo.
                    <?php if (! $pushGlobal): ?><br><span style="color:#b45309;">⚠️ El canal push está desactivado a nivel plataforma por ahora.</span><?php endif; ?>
                </div>
            </span>
        </label>
    </fieldset>

    <fieldset>
        <legend>Zona horaria</legend>
        <div class="field">
            <label>Mostrar fechas y horas en</label>
            <select name="timezone">
                <option value="">— Usar la del condominio (<?= esc($condoTz) ?>) —</option>
                <?php foreach ($zones as $z => $lbl): ?>
                    <option value="<?= esc($z) ?>" <?= ($prefs['timezone'] ?? '') === $z ? 'selected' : '' ?>><?= esc($lbl) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="muted" style="font-size:.8rem; margin-top:.25rem;">Por defecto se usa la zona horaria del condominio.</div>
        </div>
    </fieldset>

    <div class="form-actions">
        <button type="submit" class="btn">Guardar preferencias</button>
    </div>
</form>

<?php if (env('push.publicKey')): ?>
<div class="card" style="max-width:560px; margin-top:1rem;">
    <h3 style="margin:0 0 .5rem; font-size:1rem;">Permiso del navegador</h3>
    <p class="muted" style="font-size:.87rem; margin:0 0 .8rem;">
        Estado en este dispositivo: <strong id="push-status">—</strong>.
        El permiso se guarda en el navegador y las notificaciones llegan aunque cierres la sesión;
        se mantienen hasta que las desactives aquí o borres los datos del sitio.
    </p>
    <button type="button" class="btn secondary" id="push-toggle">🔔 Activar notificaciones push</button>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?php if (env('push.publicKey')): ?>
<?= $this->section('scripts') ?>
<script>
    window.KAAN_VAPID = <?= json_encode(env('push.publicKey')) ?>;
    window.KAAN_BASE  = <?= json_encode(base_url()) ?>;
</script>
<script src="<?= base_url('js/push.js') ?>"></script>
<?= $this->endSection() ?>
<?php endif; ?>
