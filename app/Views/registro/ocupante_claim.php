<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div style="max-width:460px; margin:1.5rem auto;">
    <h1 style="margin-bottom:.25rem;">Ya tienes una cuenta</h1>
    <p class="muted" style="margin-top:0;">El correo <strong><?= esc($email) ?></strong> ya está registrado en erpKaan.
        Ingresa tu contraseña para vincularte a esta casa.</p>

    <?php if (! empty($errors)): ?>
        <div class="alert error"><ul style="margin:0; padding-left:1.1rem;">
            <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
        </ul></div>
    <?php endif; ?>

    <form class="card" method="post" action="<?= site_url('registro/' . esc($token, 'url')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="email" value="<?= esc($email) ?>">
        <input type="hidden" name="nombre" value="<?= esc($nombre ?? '') ?>">
        <input type="hidden" name="telefono" value="<?= esc($telefono ?? '') ?>">

        <div class="field">
            <label>Tu contraseña *</label>
            <input type="password" name="password" required autofocus>
        </div>

        <fieldset style="margin-bottom:1rem;">
            <legend>¿Qué deseas hacer?</legend>
            <label style="display:flex; gap:.5rem; align-items:flex-start; margin-bottom:.6rem; font-weight:400;">
                <input type="radio" name="mode" value="agregar" checked style="width:auto; margin-top:.2rem;">
                <span><strong>Agregar esta casa</strong><br>
                    <span class="muted" style="font-size:.85rem;">Conservo las casas que ya ocupo y sumo esta nueva.</span></span>
            </label>
            <label style="display:flex; gap:.5rem; align-items:flex-start; font-weight:400;">
                <input type="radio" name="mode" value="mudar" style="width:auto; margin-top:.2rem;">
                <span><strong>Mudarme a esta casa</strong><br>
                    <span class="muted" style="font-size:.85rem;">Dejo de ocupar mis casas anteriores en este condominio y quedo solo en esta.</span></span>
            </label>
        </fieldset>

        <button type="submit" class="btn">Vincular y continuar</button>
    </form>
</div>

<?= $this->endSection() ?>
