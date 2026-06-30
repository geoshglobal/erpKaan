<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
use App\Models\PersonaModel;

$val  = static fn (string $f, $default = '') => old($f, is_array($ocupacion ?? null) ? ($ocupacion[$f] ?? $default) : $default);
$usos = ['propio' => 'Uso propio', 'renta_lineal' => 'Renta lineal', 'renta_vacacional' => 'Renta vacacional'];
$tipoSel = (string) $val('tipo_uso', 'propio');
$isEdit  = is_array($ocupacion ?? null);
?>

<?= $this->include('partials/propiedades_nav', ['current' => 'casas']) ?>

<div class="page-head">
    <div>
        <h1 style="margin-bottom:.2rem;"><?= esc($title) ?></h1>
        <span class="muted">Casa <strong><?= esc($casa['identificador']) ?></strong></span>
    </div>
    <a class="btn secondary" href="<?= site_url('casas/' . $casa['id'] . '/ocupaciones') ?>">← Ocupaciones</a>
</div>

<?php if (session('error')): ?><div class="alert error"><?= esc(session('error')) ?></div><?php endif; ?>
<?php if (session('success')): ?><div class="alert success"><?= esc(session('success')) ?></div><?php endif; ?>
<?php if (session('errors')): ?>
    <div class="alert error"><ul style="margin:0; padding-left:1.1rem;">
        <?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul></div>
<?php endif; ?>

<form class="card" method="post" action="<?= esc($action) ?>">
    <?= csrf_field() ?>
    <fieldset>
        <legend>Datos de la ocupación</legend>
        <div class="grid2">
            <div class="field">
                <label>Tipo de uso</label>
                <select name="tipo_uso">
                    <?php foreach ($usos as $k => $label): ?>
                        <option value="<?= $k ?>" <?= $k === $tipoSel ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label style="font-weight:400; display:flex; align-items:center; gap:.5rem; margin-top:1.7rem;">
                    <input type="checkbox" name="vigente" value="1" style="width:auto;" <?= (int) $val('vigente', $isEdit ? 0 : 1) === 1 ? 'checked' : '' ?>>
                    Ocupación vigente (actualiza el uso actual de la casa)
                </label>
            </div>
        </div>
        <div class="grid2">
            <div class="field">
                <label>Fecha inicio</label>
                <input type="text" name="fecha_inicio" value="<?= esc($val('fecha_inicio')) ?>" placeholder="AAAA-MM-DD">
            </div>
            <div class="field">
                <label>Fecha fin</label>
                <input type="text" name="fecha_fin" value="<?= esc($val('fecha_fin')) ?>" placeholder="AAAA-MM-DD">
            </div>
        </div>
        <div class="grid2">
            <div class="field">
                <label>Monto de renta</label>
                <input type="text" name="renta_monto" value="<?= esc($val('renta_monto')) ?>" placeholder="0.00">
            </div>
            <div class="field">
                <label>Depósito</label>
                <input type="text" name="deposito" value="<?= esc($val('deposito')) ?>" placeholder="0.00">
            </div>
        </div>
        <div class="field">
            <label>Notas</label>
            <input type="text" name="notas" value="<?= esc($val('notas')) ?>">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn">Guardar</button>
            <a class="btn secondary" href="<?= site_url('casas/' . $casa['id'] . '/ocupaciones') ?>">Cancelar</a>
        </div>
    </fieldset>
</form>

<?php if ($isEdit): ?>
    <div class="card" style="margin-top:1.5rem;">
        <h2 style="margin:0 0 .75rem; font-size:1.05rem;">Ocupantes</h2>

        <?php if ($ocupantes === []): ?>
            <p class="muted">Sin ocupantes. Agrega el principal y, si aplica, los secundarios.</p>
        <?php else: ?>
            <table class="grid" style="margin-bottom:1.25rem;">
                <thead><tr><th>Ocupante</th><th>Rol</th><th>Parentesco</th><th style="text-align:right;">Acciones</th></tr></thead>
                <tbody>
                    <?php foreach ($ocupantes as $oc): ?>
                        <tr>
                            <td><strong><?= esc(PersonaModel::fullName($oc)) ?></strong></td>
                            <td>
                                <?php if ($oc['rol'] === 'principal'): ?>
                                    <span class="pill on">Principal</span>
                                <?php else: ?>
                                    <span class="muted">Secundario</span>
                                <?php endif; ?>
                            </td>
                            <td class="muted"><?= esc($oc['parentesco'] ?: '—') ?></td>
                            <td style="text-align:right; white-space:nowrap;">
                                <?php if ($oc['rol'] !== 'principal'): ?>
                                    <form method="post" action="<?= site_url('casas/' . $casa['id'] . '/ocupaciones/' . $ocupacion['id'] . '/ocupantes/' . $oc['id'] . '/principal') ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button class="btn secondary small">Hacer principal</button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" action="<?= site_url('casas/' . $casa['id'] . '/ocupaciones/' . $ocupacion['id'] . '/ocupantes/' . $oc['id'] . '/eliminar') ?>"
                                      style="display:inline;" onsubmit="return confirm('¿Quitar a este ocupante?');">
                                    <?= csrf_field() ?>
                                    <button class="btn danger small">Quitar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <form method="post" action="<?= site_url('casas/' . $casa['id'] . '/ocupaciones/' . $ocupacion['id'] . '/ocupantes') ?>">
            <?= csrf_field() ?>
            <fieldset>
                <legend>Agregar ocupante</legend>
                <?php if ($personas === []): ?>
                    <p class="muted">No hay personas en este condominio.
                        <a href="<?= site_url('personas/nueva') ?>">Crea una</a> primero.</p>
                <?php else: ?>
                    <div class="grid2">
                        <div class="field">
                            <label>Persona *</label>
                            <select name="persona_id" required>
                                <option value="">— Selecciona —</option>
                                <?php foreach ($personas as $p): ?>
                                    <option value="<?= (int) $p['id'] ?>"><?= esc(PersonaModel::fullName($p)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label>Rol</label>
                            <select name="rol">
                                <option value="secundario">Secundario</option>
                                <option value="principal">Principal</option>
                            </select>
                        </div>
                    </div>
                    <div class="field">
                        <label>Parentesco / relación</label>
                        <input type="text" name="parentesco" placeholder="Ej. cónyuge, hijo, arrendatario">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn">Agregar ocupante</button>
                    </div>
                <?php endif; ?>
            </fieldset>
        </form>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
