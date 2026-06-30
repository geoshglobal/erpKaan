<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
use App\Models\AccesoModel;

$estado = AccesoModel::estadoEfectivo($acceso);
$tipos  = ['visita' => 'Visita', 'paqueteria' => 'Paquetería', 'delivery' => 'Delivery', 'proveedor' => 'Proveedor'];
?>

<div class="page-head">
    <h1><?= esc($acceso['nombre_visitante']) ?></h1>
    <a class="btn secondary" href="<?= site_url('accesos') ?>">← Accesos</a>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
    <div class="card">
        <h2 style="margin:0 0 .6rem; font-size:1.05rem;">Detalle</h2>
        <p style="margin:.25rem 0;"><span class="muted">Tipo:</span> <?= esc($tipos[$acceso['tipo']] ?? $acceso['tipo']) ?></p>
        <p style="margin:.25rem 0;"><span class="muted">Casa:</span> <strong><?= esc($casa['identificador'] ?? '') ?></strong></p>
        <p style="margin:.25rem 0;"><span class="muted">Personas:</span> <?= (int) $acceso['num_personas'] ?></p>
        <?php if ($acceso['empresa']): ?><p style="margin:.25rem 0;"><span class="muted">Motivo/empresa:</span> <?= esc($acceso['empresa']) ?></p><?php endif; ?>
        <?php if ($acceso['telefono']): ?><p style="margin:.25rem 0;"><span class="muted">Teléfono:</span> <?= esc($acceso['telefono']) ?></p><?php endif; ?>
        <?php if ($acceso['placas']): ?><p style="margin:.25rem 0;"><span class="muted">Placas:</span> <?= esc($acceso['placas']) ?></p><?php endif; ?>
        <p style="margin:.25rem 0;"><span class="muted">Vigencia:</span>
            <?= $acceso['valido_desde'] ? esc(date('d/m/Y H:i', strtotime($acceso['valido_desde']))) : '—' ?>
            <?= $acceso['valido_hasta'] ? ' → ' . esc(date('d/m/Y H:i', strtotime($acceso['valido_hasta']))) : '' ?></p>
        <p style="margin:.5rem 0 0;"><span class="pill <?= in_array($estado, ['programado', 'ingresado'], true) ? 'on' : 'off' ?>"><?= esc(AccesoModel::ESTADOS[$estado] ?? $estado) ?></span></p>
        <?php if ($acceso['check_in_at']): ?><p class="muted" style="margin:.5rem 0 0; font-size:.85rem;">Entrada: <?= esc($acceso['check_in_at']) ?></p><?php endif; ?>
        <?php if ($acceso['check_out_at']): ?><p class="muted" style="margin:.1rem 0 0; font-size:.85rem;">Salida: <?= esc($acceso['check_out_at']) ?></p><?php endif; ?>
    </div>

    <div class="card">
        <h2 style="margin:0 0 .6rem; font-size:1.05rem;">Bitácora</h2>
        <?php if ($eventos === []): ?>
            <p class="muted" style="margin:0;">Sin eventos.</p>
        <?php else: ?>
            <ul style="margin:0; padding-left:1.1rem; font-size:.9rem;">
                <?php foreach ($eventos as $e): ?>
                    <li style="margin-bottom:.4rem;">
                        <strong><?= esc(AccesoModel::ESTADOS[$e['estado_nuevo']] ?? $e['estado_nuevo']) ?></strong>
                        <span class="muted">— <?= esc(date('d/m/Y H:i', strtotime($e['created_at']))) ?></span>
                        <?php if ($e['nota']): ?><br><span class="muted" style="font-size:.85rem;"><?= esc($e['nota']) ?></span><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
