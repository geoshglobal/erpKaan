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
// Preserve other filters when switching the tipo tab.
$tabQuery = static function (string $tipo) use ($filters): string {
    $q = array_filter(['casa_id' => $filters['casa_id'], 'q' => $filters['q'], 'estado' => $filters['estado'], 'tipo' => $tipo]);
    return $q === [] ? '' : '?' . http_build_query($q);
};
?>

<div class="tabs">
    <a href="<?= site_url('accesos') . $tabQuery('') ?>" class="<?= $filters['tipo'] === '' ? 'active' : '' ?>">Todos</a>
    <?php foreach ($tipos as $key => $lbl): ?>
        <a href="<?= site_url('accesos') . $tabQuery($key) ?>" class="<?= $filters['tipo'] === $key ? 'active' : '' ?>"><?= esc($lbl) ?></a>
    <?php endforeach; ?>
</div>

<form class="filters" method="get" action="<?= site_url('accesos') ?>">
    <?php if ($filters['tipo'] !== ''): ?><input type="hidden" name="tipo" value="<?= esc($filters['tipo'], 'attr') ?>"><?php endif; ?>
    <div class="field">
        <label>Departamento</label>
        <select name="casa_id" onchange="this.form.submit()">
            <option value="">Todos</option>
            <?php foreach ($casas as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= (int) $filters['casa_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['identificador']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field" style="flex:1; min-width:180px;">
        <label>Buscar (visitante, casa, empresa)</label>
        <input type="text" name="q" value="<?= esc($filters['q'], 'attr') ?>" placeholder="Ej. Juan, A-101, Amazon">
    </div>
    <button type="submit" class="btn">Buscar</button>
    <?php if ($filters['casa_id'] || $filters['q'] || $filters['estado']): ?>
        <a class="btn secondary" href="<?= site_url('accesos') . ($filters['tipo'] !== '' ? '?tipo=' . esc($filters['tipo'], 'url') : '') ?>">Limpiar</a>
    <?php endif; ?>
</form>

<?php if ($accesos === []): ?>
    <p class="muted">No hay accesos que coincidan con el filtro.</p>
<?php else: ?>
    <div class="table-wrap">
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
                        <?= $a['valido_desde'] ? esc(dt($a['valido_desde'], 'd/m H:i')) : '—' ?>
                        <?= $a['valido_hasta'] ? ' → ' . esc(dt($a['valido_hasta'], 'd/m H:i')) : '' ?>
                    </td>
                    <td><span class="pill <?= $pillClass[$estado] ?? 'off' ?>"><?= esc(AccesoModel::ESTADOS[$estado] ?? $estado) ?></span></td>
                    <td style="text-align:right;">
                        <a class="btn secondary small" href="<?= site_url('accesos/' . $a['id']) ?>">Ver</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?= $pager->links('default', 'kaan') ?>
<?php endif; ?>

<?= $this->endSection() ?>
