<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php use App\Models\AccesoModel; ?>

<div class="page-head">
    <h1>Paquetería y entregas</h1>
    <div class="head-actions">
        <a class="btn" href="<?= site_url('portal/avisos/nuevo') ?>">🛵 Avisar delivery/proveedor</a>
        <a class="btn secondary" href="<?= site_url('portal') ?>">← Mi portal</a>
    </div>
</div>

<?= $this->include('partials/date_filter', ['range' => $range, 'action' => site_url('portal/paquetes')]) ?>

<?php
$pill = ['en_caseta' => 'on', 'entregado' => 'off', 'ingresado' => 'on', 'finalizado' => 'off', 'cancelado' => 'off'];
?>

<?php if ($paquetes === []): ?>
    <p class="muted">No tienes paquetería ni entregas en este rango.</p>
<?php else: ?>
    <div class="cards-list">
        <?php foreach ($paquetes as $p): ?>
            <?php $estado = AccesoModel::estadoEfectivo($p); ?>
            <div class="card list-card">
                <div style="display:flex; gap:.9rem; align-items:flex-start;">
                    <?php if (! empty($p['foto_path']) || ! empty($p['foto_entrega_path'])): ?>
                        <div style="display:flex; gap:.35rem;">
                            <?php if (! empty($p['foto_path'])): ?>
                                <a href="<?= base_url(esc($p['foto_path'])) ?>" target="_blank" title="Paquete">
                                    <img src="<?= base_url(esc($p['foto_path'])) ?>" alt="Paquete" style="width:54px; height:54px; object-fit:cover; border-radius:8px; border:1px solid #cbd5e1;">
                                </a>
                            <?php endif; ?>
                            <?php if (! empty($p['foto_entrega_path'])): ?>
                                <a href="<?= base_url(esc($p['foto_entrega_path'])) ?>" target="_blank" title="Entrega">
                                    <img src="<?= base_url(esc($p['foto_entrega_path'])) ?>" alt="Entrega" style="width:54px; height:54px; object-fit:cover; border-radius:8px; border:1px solid #16a34a;">
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div style="width:54px; height:54px; border-radius:8px; background:#f1f5f9; display:flex; align-items:center; justify-content:center; font-size:1.4rem;">
                            <?= $p['tipo'] === 'paqueteria' ? '📦' : ($p['tipo'] === 'proveedor' ? '🔧' : '🛵') ?>
                        </div>
                    <?php endif; ?>
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; justify-content:space-between; gap:.6rem; align-items:baseline;">
                            <strong><?= esc($p['nombre_visitante']) ?></strong>
                            <span class="pill <?= $pill[$estado] ?? 'off' ?>" style="white-space:nowrap;"><?= esc(AccesoModel::ESTADOS[$estado] ?? $estado) ?></span>
                        </div>
                        <div class="muted" style="font-size:.85rem;">
                            <?= esc(AccesoModel::TIPOS[$p['tipo']] ?? $p['tipo']) ?>
                            <?= $p['empresa'] ? ' · ' . esc($p['empresa']) : '' ?>
                            · <?= esc($p['casa_ident'] ?? '') ?>
                        </div>
                        <div class="muted" style="font-size:.8rem; margin-top:.15rem;">
                            <?php if ($p['tipo'] === 'paqueteria'): ?>
                                Recibido: <?= esc(dt($p['created_at'], 'd/m/Y H:i')) ?>
                                <?= ! empty($p['check_out_at']) ? ' · Entregado: ' . esc(dt($p['check_out_at'], 'd/m H:i')) : '' ?>
                            <?php else: ?>
                                Ingreso: <?= ! empty($p['check_in_at']) ? esc(dt($p['check_in_at'], 'd/m/Y H:i')) : '—' ?>
                                <?= ! empty($p['check_out_at']) ? ' · Salida: ' . esc(dt($p['check_out_at'], 'd/m H:i')) : '' ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?= $pager->links('default', 'kaan') ?>
<?php endif; ?>

<?= $this->endSection() ?>
