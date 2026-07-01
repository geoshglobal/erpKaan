<?php
use App\Models\AccesoModel;

$estado = AccesoModel::estadoEfectivo($acceso);
?>
<div style="margin-top:1rem;">
    <?php if ($estado === 'en_caseta'): ?>
        <a class="btn" href="<?= site_url('caseta/accesos/' . $acceso['id'] . '/entregar') ?>">📬 Marcar entregado</a>
    <?php elseif (in_array($estado, ['programado', 'vencido'], true)): ?>
        <?php if ($estado === 'vencido'): ?>
            <p class="muted" style="font-size:.82rem; margin:.25rem 0;">⚠️ Pase fuera de su ventana de vigencia.</p>
        <?php endif; ?>
        <a class="btn" href="<?= site_url('caseta/accesos/' . $acceso['id'] . '/checkin') ?>">Registrar entrada</a>
    <?php elseif ($estado === 'ingresado'): ?>
        <form method="post" action="<?= site_url('caseta/accesos/' . $acceso['id'] . '/checkout') ?>" style="display:inline;">
            <?= csrf_field() ?>
            <button class="btn">Registrar salida</button>
        </form>
    <?php else: ?>
        <p class="muted" style="margin:0;">Sin acciones disponibles (<?= esc(AccesoModel::ESTADOS[$estado] ?? $estado) ?>).</p>
    <?php endif; ?>

    <?php if (in_array($acceso['tipo'], ['paqueteria', 'delivery', 'proveedor'], true) && in_array($estado, ['en_caseta', 'programado', 'ingresado'], true)): ?>
        <a class="btn secondary small" style="margin-left:.4rem;" href="<?= site_url('caseta/accesos/' . $acceso['id'] . '/reasignar') ?>">✏️ Corregir casa</a>
    <?php endif; ?>
</div>
