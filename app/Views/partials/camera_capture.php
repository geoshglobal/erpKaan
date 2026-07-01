<?php
/**
 * Reusable live-camera capture widget bound to a file input.
 * Params: $inputId (the file input's id). Load public/js/camera.js on the page.
 */
$inputId = $inputId ?? 'foto';
?>
<div class="cam-widget" data-input="<?= esc($inputId) ?>" style="margin-top:.5rem;">
    <button type="button" class="btn secondary small cam-open">📷 Tomar foto</button>
    <video class="cam-video" playsinline muted style="display:none; width:100%; max-width:320px; margin-top:.5rem; border-radius:8px; background:#000;"></video>
    <div class="cam-actions" style="display:none; gap:.5rem; margin-top:.5rem;">
        <button type="button" class="btn small cam-capture">Capturar</button>
        <button type="button" class="btn secondary small cam-cancel">Cancelar</button>
    </div>
    <canvas class="cam-canvas" style="display:none;"></canvas>
    <img class="cam-preview" alt="" style="display:none; max-width:200px; border-radius:8px; margin-top:.5rem; border:1px solid #cbd5e1;">
</div>
