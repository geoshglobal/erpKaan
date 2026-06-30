<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php use App\Models\PersonaModel; ?>

<div style="max-width:460px; margin:1.5rem auto;">
    <h1 style="margin-bottom:.25rem;">Crear tu cuenta</h1>
    <p class="muted" style="margin-top:0;">
        Hola <strong><?= esc(PersonaModel::fullName($persona)) ?></strong>, define tu acceso a erpKaan
        (rol: <strong><?= esc($inv['rol']) ?></strong>).
    </p>

    <?php if (session('errors')): ?>
        <div class="alert error"><ul style="margin:0; padding-left:1.1rem;">
            <?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
        </ul></div>
    <?php endif; ?>

    <form class="card" method="post" action="<?= site_url('registro/' . esc($token, 'url')) ?>">
        <?= csrf_field() ?>
        <div class="field">
            <label>Correo *</label>
            <input type="email" name="email" value="<?= esc(old('email', $inv['email'] ?? ($persona['email'] ?? ''))) ?>" required>
        </div>
        <div class="field">
            <label>Contraseña * (mín. 8 caracteres)</label>
            <input type="password" name="password" required>
        </div>
        <div class="field">
            <label>Confirmar contraseña *</label>
            <input type="password" name="password_confirm" required>
        </div>
        <button type="submit" class="btn">Crear cuenta e ingresar</button>
    </form>
</div>

<?= $this->endSection() ?>
