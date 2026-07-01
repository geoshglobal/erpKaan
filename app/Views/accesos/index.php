<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php use App\Models\AccesoModel; ?>

<div class="page-head">
    <h1>Accesos del condominio</h1>
    <?php if (auth()->user()->can('caseta.operate')): ?>
        <div class="head-actions">
            <a class="btn secondary" href="<?= site_url('caseta/registro') ?>">📦 Paquetería / entrega</a>
            <a class="btn" href="<?= site_url('caseta/escaner') ?>">📷 Escanear QR</a>
        </div>
    <?php endif; ?>
</div>

<?php
$pillClass = ['programado' => 'on', 'ingresado' => 'on', 'en_caseta' => 'on', 'entregado' => 'off', 'finalizado' => 'off', 'cancelado' => 'off', 'vencido' => 'off'];
$tipos = AccesoModel::TIPOS;
?>

<?php if ($accesos === []): ?>
    <p class="muted">No hay accesos registrados en este condominio.</p>
<?php else: ?>
    <table class="grid">
        <thead>
            <tr>
                <th>Visitante</th>
                <th>Tipo</th>
                <th>Casa</th>
                <th>Solicitó</th>
                <th>Vigencia</th>
                <th>Estado</th>
                <th style="text-align:right;"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($accesos as $a): ?>
                <?php $estado = AccesoModel::estadoEfectivo($a); ?>
                <tr>
                    <td><strong><?= esc($a['nombre_visitante']) ?></strong></td>
                    <td><?= esc($tipos[$a['tipo']] ?? $a['tipo']) ?></td>
                    <td class="muted"><?= esc($a['casa_ident'] ?? '') ?></td>
                    <td class="muted"><?= esc($a['solicitante'] ?: '—') ?></td>
                    <td class="muted" style="font-size:.85rem;">
                        <?= $a['valido_desde'] ? esc(date('d/m H:i', strtotime($a['valido_desde']))) : '—' ?>
                        <?= $a['valido_hasta'] ? ' → ' . esc(date('d/m H:i', strtotime($a['valido_hasta']))) : '' ?>
                    </td>
                    <td><span class="pill <?= $pillClass[$estado] ?? 'off' ?>"><?= esc(AccesoModel::ESTADOS[$estado] ?? $estado) ?></span></td>
                    <td style="text-align:right;">
                        <a class="btn secondary small" href="<?= site_url('accesos/' . $a['id']) ?>">Ver</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?= $this->endSection() ?>
