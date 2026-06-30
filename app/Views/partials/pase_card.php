<?php
use App\Models\AccesoModel;

$estado = AccesoModel::estadoEfectivo($acceso);
$estadoLabel = AccesoModel::ESTADOS[$estado] ?? $estado;
$ok = in_array($estado, ['programado', 'ingresado'], true);
?>
<div class="card" style="max-width:420px; margin:0 auto; text-align:center;">
    <div style="font-weight:700; font-size:1.1rem; margin-bottom:.25rem;"><?= esc($acceso['nombre_visitante']) ?></div>
    <div class="muted" style="margin-bottom:1rem;">
        Casa <strong><?= esc($casaIdent ?? '') ?></strong>
        <?= $acceso['num_personas'] > 1 ? ' · ' . (int) $acceso['num_personas'] . ' personas' : '' ?>
    </div>

    <div id="qrcode" data-url="<?= esc($passUrl, 'attr') ?>"
         style="display:inline-block; padding:12px; background:#fff; border:1px solid #e2e8f0; border-radius:12px;"></div>

    <div style="margin-top:1rem;">
        <span class="pill <?= $ok ? 'on' : 'off' ?>" style="font-size:.85rem;"><?= esc($estadoLabel) ?></span>
    </div>

    <div class="muted" style="font-size:.85rem; margin-top:1rem; text-align:left;">
        <?php if ($acceso['empresa']): ?><div>Motivo: <?= esc($acceso['empresa']) ?></div><?php endif; ?>
        <?php if ($acceso['placas']): ?><div>Placas: <?= esc($acceso['placas']) ?></div><?php endif; ?>
        <div>Vigencia:
            <?= $acceso['valido_desde'] ? esc(date('d/m/Y H:i', strtotime($acceso['valido_desde']))) : '—' ?>
            <?= $acceso['valido_hasta'] ? ' → ' . esc(date('d/m/Y H:i', strtotime($acceso['valido_hasta']))) : '' ?>
        </div>
    </div>
</div>
