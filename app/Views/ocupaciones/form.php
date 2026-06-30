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

        <hr style="border:none; border-top:1px solid #e2e8f0; margin:1.25rem 0;">

        <h3 style="margin:0 0 .5rem; font-size:1rem;">Invitar ocupante con cuenta</h3>
        <p class="muted" style="margin-top:0; font-size:.85rem;">Genera un enlace para que el ocupante cree su cuenta
            (necesaria para generar pases QR). Se ligará a esta casa automáticamente.</p>

        <?php $pendientes = array_values(array_filter($invitaciones, static fn ($i) => $i['tipo'] === 'ocupante')); ?>
        <?php if ($pendientes !== []): ?>
            <?php foreach ($pendientes as $idx => $inv): ?>
                <?php $link = site_url('registro/' . $inv['token']); ?>
                <div style="background:#fefce8; border:1px solid #fde68a; border-radius:8px; padding:.6rem .8rem; margin-bottom:.6rem;">
                    <div class="muted" style="font-size:.82rem; margin-bottom:.35rem;">
                        Pendiente — <?= esc($inv['nombre'] ?: $inv['email'] ?: 'nuevo ocupante') ?>
                        (<?= esc($inv['rol_ocupante']) ?>, login <?= esc($inv['rol']) ?>)
                    </div>
                    <div style="display:flex; gap:.5rem;">
                        <input type="text" readonly value="<?= esc($link) ?>" id="inv-<?= $idx ?>" onclick="this.select()"
                               style="flex:1; padding:.4rem .5rem; border:1px solid #cbd5e1; border-radius:6px; font-size:.8rem; background:#fff;">
                        <button type="button" class="btn small" id="invbtn-<?= $idx ?>"
                                onclick="navigator.clipboard.writeText(document.getElementById('inv-<?= $idx ?>').value).then(()=>{var b=document.getElementById('invbtn-<?= $idx ?>');b.textContent='¡Copiado!';setTimeout(()=>b.textContent='Copiar',1500);})">Copiar</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="post" action="<?= site_url('casas/' . $casa['id'] . '/ocupaciones/' . $ocupacion['id'] . '/invitar-ocupante') ?>">
            <?= csrf_field() ?>
            <fieldset>
                <legend>Nueva invitación</legend>
                <div class="grid2">
                    <div class="field">
                        <label>Rol del ocupante</label>
                        <select name="rol_ocupante">
                            <option value="secundario">Secundario</option>
                            <option value="principal">Principal</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Rol de acceso (login)</label>
                        <select name="rol">
                            <option value="inquilino">inquilino</option>
                            <option value="dueno">dueno</option>
                            <option value="huesped">huesped</option>
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label>Persona existente (sin cuenta) — opcional</label>
                    <select name="persona_id">
                        <option value="">— Nueva persona (captura sus datos al registrarse) —</option>
                        <?php foreach ($personas as $p): ?>
                            <?php if (empty($p['user_id'])): ?>
                                <option value="<?= (int) $p['id'] ?>"><?= esc(\App\Models\PersonaModel::fullName($p)) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid2">
                    <div class="field">
                        <label>Nombre sugerido (si es nueva)</label>
                        <input type="text" name="nombre" placeholder="Opcional">
                    </div>
                    <div class="field">
                        <label>Correo sugerido</label>
                        <input type="email" name="email" placeholder="Opcional">
                    </div>
                </div>
                <button type="submit" class="btn secondary">Generar invitación</button>
            </fieldset>
        </form>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
