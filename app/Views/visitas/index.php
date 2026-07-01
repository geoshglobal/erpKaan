<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php use App\Models\AccesoModel; ?>

<div class="page-head">
    <h1>Mis visitas</h1>
    <a class="btn" href="<?= site_url('portal/visitas/nueva') ?>">+ Nueva visita</a>
</div>


<?= $this->include('partials/date_filter', ['range' => $range, 'action' => site_url('portal/visitas')]) ?>

<?php
$pillClass = ['programado' => 'on', 'ingresado' => 'on', 'finalizado' => 'off', 'cancelado' => 'off', 'vencido' => 'off'];
?>

<?php if ($visitas === []): ?>
    <p class="muted">No tienes visitas en este rango.</p>
<?php else: ?>
    <table class="grid">
        <thead>
            <tr>
                <th>Visitante</th>
                <th>Casa</th>
                <th>Vigencia</th>
                <th>Estado</th>
                <th style="text-align:right;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($visitas as $v): ?>
                <?php $estado = AccesoModel::estadoEfectivo($v); ?>
                <tr>
                    <td><strong><?= esc($v['nombre_visitante']) ?></strong>
                        <?= $v['num_personas'] > 1 ? '<span class="muted">+' . ((int) $v['num_personas'] - 1) . '</span>' : '' ?></td>
                    <td class="muted"><?= esc($v['casa_ident'] ?? '') ?></td>
                    <td class="muted" style="font-size:.85rem;">
                        <?= $v['valido_desde'] ? esc(date('d/m/Y H:i', strtotime($v['valido_desde']))) : '—' ?>
                        <?= $v['valido_hasta'] ? ' → ' . esc(date('d/m H:i', strtotime($v['valido_hasta']))) : '' ?>
                    </td>
                    <td><span class="pill <?= $pillClass[$estado] ?? 'off' ?>"><?= esc(AccesoModel::ESTADOS[$estado] ?? $estado) ?></span></td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a class="btn secondary small" href="<?= site_url('portal/visitas/' . $v['id']) ?>">Ver QR</a>
                        <?php if ($estado === 'programado'): ?>
                            <form method="post" action="<?= site_url('portal/visitas/' . $v['id'] . '/cancelar') ?>"
                                  style="display:inline;" onsubmit="return confirm('¿Cancelar esta visita?');">
                                <?= csrf_field() ?>
                                <button class="btn danger small">Cancelar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?= $pager->links('default', 'kaan') ?>
<?php endif; ?>

<?= $this->endSection() ?>
