<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php
use App\Models\PersonaModel;

$nombrePrefill = $persona !== null ? PersonaModel::fullName($persona) : ($inv['nombre'] ?? '');
$emailPrefill  = $inv['email'] ?? ($persona['email'] ?? '');
?>

<div style="max-width:460px; margin:1.5rem auto;">
    <h1 style="margin-bottom:.25rem;">Crear tu cuenta</h1>
    <p class="muted" style="margin-top:0;">Te invitaron como ocupante (<strong><?= esc($inv['rol_ocupante']) ?></strong>).
        Crea tu acceso a erpKaan.</p>

    <?php if (! empty($errors)): ?>
        <div class="alert error"><ul style="margin:0; padding-left:1.1rem;">
            <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
        </ul></div>
    <?php endif; ?>

    <form class="card" method="post" action="<?= site_url('registro/' . esc($token, 'url')) ?>">
        <?= csrf_field() ?>
        <div class="field">
            <label>Nombre completo *</label>
            <input type="text" name="nombre" value="<?= esc($nombrePrefill) ?>" <?= $persona !== null ? 'readonly' : '' ?> required>
        </div>
        <div class="field">
            <label>Correo *</label>
            <input type="email" name="email" value="<?= esc($emailPrefill) ?>" required>
        </div>
        <div class="field">
            <label>Teléfono</label>
            <input type="text" name="telefono" value="">
        </div>
        <div class="field">
            <label>Contraseña * (mín. 8)</label>
            <input type="password" name="password" required>
        </div>
        <div class="field">
            <label>Confirmar contraseña *</label>
            <input type="password" name="password_confirm" required>
        </div>
        <button type="submit" class="btn">Crear cuenta e ingresar</button>
    </form>
    <p class="muted" style="font-size:.8rem; text-align:center;">Si ya tienes cuenta en erpKaan, usa el mismo correo
        y te pediremos tu contraseña para vincularte.</p>
</div>

<?= $this->endSection() ?>
