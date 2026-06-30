<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php use App\Models\PersonaModel; ?>

<div class="page-head">
    <div>
        <h1 style="margin-bottom:.2rem;">Ocupantes de mi casa</h1>
        <span class="muted">Casa <strong><?= esc($casa['identificador']) ?></strong></span>
    </div>
    <a class="btn secondary" href="<?= site_url('portal') ?>">← Mi portal</a>
</div>


<?php
$invByPersona = [];
foreach ($invitaciones as $inv) {
    if ($inv['tipo'] === 'ocupante' && $inv['persona_id']) {
        $invByPersona[(int) $inv['persona_id']] = $inv;
    }
}
?>

<table class="grid" style="margin-bottom:1.5rem;">
    <thead><tr><th>Ocupante</th><th>Rol</th><th>Cuenta</th><th style="text-align:right;">Acciones</th></tr></thead>
    <tbody>
        <?php foreach ($ocupantes as $oc): ?>
            <tr>
                <td><strong><?= esc(PersonaModel::fullName($oc)) ?></strong></td>
                <td><?= $oc['rol'] === 'principal' ? '<span class="pill on">Principal</span>' : '<span class="muted">Secundario</span>' ?></td>
                <td>
                    <?php if (! empty($oc['user_id'])): ?>
                        <span class="pill on">Con cuenta</span>
                    <?php elseif (isset($invByPersona[(int) $oc['persona_id']])): ?>
                        <?php $lnk = site_url('registro/' . $invByPersona[(int) $oc['persona_id']]['token']); ?>
                        <span class="muted" style="font-size:.8rem;">Invitación pendiente</span>
                        <button type="button" class="btn small" onclick="navigator.clipboard.writeText('<?= esc($lnk, 'js') ?>').then(()=>{this.textContent='¡Copiado!';setTimeout(()=>this.textContent='Copiar enlace',1500);})">Copiar enlace</button>
                    <?php else: ?>
                        <span class="muted" style="font-size:.8rem;">Sin cuenta</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:right; white-space:nowrap;">
                    <?php if (empty($oc['user_id']) && ! isset($invByPersona[(int) $oc['persona_id']])): ?>
                        <form method="post" action="<?= site_url('portal/ocupacion/' . $ocupacion['id'] . '/ocupantes/' . $oc['id'] . '/invitar') ?>" style="display:inline;">
                            <?= csrf_field() ?>
                            <button class="btn secondary small">Invitar (dar cuenta)</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($oc['rol'] !== 'principal'): ?>
                        <form method="post" action="<?= site_url('portal/ocupacion/' . $ocupacion['id'] . '/ocupantes/' . $oc['id'] . '/eliminar') ?>"
                              style="display:inline;" onsubmit="return confirm('¿Quitar a este ocupante?');">
                            <?= csrf_field() ?>
                            <button class="btn danger small">Quitar</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<form class="card" method="post" action="<?= site_url('portal/ocupacion/' . $ocupacion['id'] . '/ocupantes') ?>">
    <?= csrf_field() ?>
    <fieldset>
        <legend>Agregar ocupante</legend>
        <p class="muted" style="margin-top:0; font-size:.85rem;">Agrega a un familiar u ocupante con solo su nombre.
            Si necesita generar pases QR, luego puedes invitarlo a crear su cuenta.</p>
        <div class="field">
            <label>Nombre completo *</label>
            <input type="text" name="nombre" required>
        </div>
        <button type="submit" class="btn">Agregar ocupante</button>
    </fieldset>
</form>

<?= $this->endSection() ?>
