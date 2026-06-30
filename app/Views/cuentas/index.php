<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<?php use App\Models\PersonaModel; ?>

<div class="page-head">
    <div>
        <h1 style="margin-bottom:.2rem;">Acceso a la app</h1>
        <span class="muted"><?= esc(PersonaModel::fullName($persona)) ?></span>
    </div>
    <a class="btn secondary" href="<?= site_url('personas/' . $persona['id'] . '/editar') ?>">← Persona</a>
</div>

<?php if (session('error')): ?><div class="alert error"><?= esc(session('error')) ?></div><?php endif; ?>
<?php if (session('success')): ?><div class="alert success"><?= esc(session('success')) ?></div><?php endif; ?>
<?php if (session('errors')): ?>
    <div class="alert error"><ul style="margin:0; padding-left:1.1rem;">
        <?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul></div>
<?php endif; ?>

<?php if ($account !== null): ?>
    <div class="card">
        <h2 style="margin:0 0 .5rem; font-size:1.05rem;">✅ Cuenta activa</h2>
        <p class="muted" style="margin:.2rem 0;">Correo de acceso: <strong><?= esc($account->email) ?></strong></p>
        <p class="muted" style="margin:.2rem 0;">Roles: <?= esc(implode(', ', $account->getGroups())) ?></p>
        <p class="muted" style="margin:.2rem 0; font-size:.85rem;">El residente inicia sesión en
            <a href="<?= site_url('login') ?>"><?= site_url('login') ?></a> con su correo.</p>
    </div>
<?php else: ?>
    <?php if ($invitacion !== null): ?>
        <?php $invLink = site_url('registro/' . $invitacion['token']); ?>
        <div class="card" style="background:#fefce8; border-color:#fde68a; margin-bottom:1rem;">
            <h2 style="margin:0 0 .4rem; font-size:1rem; color:#854d0e;">Invitación pendiente
                <span class="muted" style="font-weight:400;">(rol <?= esc($invitacion['rol']) ?>, válida 14 días)</span></h2>
            <p class="muted" style="margin:0 0 .5rem; font-size:.85rem;">Comparte este enlace con el residente para que cree su contraseña:</p>
            <div style="display:flex; gap:.5rem; align-items:center;">
                <input type="text" readonly value="<?= esc($invLink) ?>" id="invite-link"
                       onclick="this.select()" style="flex:1; padding:.5rem .6rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.85rem; background:#fff;">
                <button type="button" class="btn small" id="copy-invite"
                        onclick="navigator.clipboard.writeText(document.getElementById('invite-link').value).then(()=>{var b=document.getElementById('copy-invite');b.textContent='¡Copiado!';setTimeout(()=>b.textContent='Copiar',1500);})">Copiar</button>
            </div>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
        <form class="card" method="post" action="<?= site_url('personas/' . $persona['id'] . '/cuenta') ?>">
            <?= csrf_field() ?>
            <h2 style="margin:0 0 .75rem; font-size:1.05rem;">Crear cuenta directamente</h2>
            <div class="field">
                <label>Correo *</label>
                <input type="email" name="email" value="<?= esc(old('email', $persona['email'] ?? '')) ?>" required>
            </div>
            <div class="field">
                <label>Contraseña * (mín. 8)</label>
                <input type="text" name="password" value="<?= esc(old('password')) ?>" required>
            </div>
            <div class="field">
                <label>Rol *</label>
                <select name="rol" required>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= esc($r) ?>" <?= old('rol') === $r ? 'selected' : '' ?>><?= esc($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn">Crear cuenta</button>
        </form>

        <form class="card" method="post" action="<?= site_url('personas/' . $persona['id'] . '/invitacion') ?>">
            <?= csrf_field() ?>
            <h2 style="margin:0 0 .75rem; font-size:1.05rem;">Generar invitación</h2>
            <p class="muted" style="font-size:.85rem; margin-top:0;">Crea un enlace para que el residente
                defina su propia contraseña.</p>
            <div class="field">
                <label>Rol *</label>
                <select name="rol" required>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= esc($r) ?>"><?= esc($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn secondary">Generar enlace de invitación</button>
        </form>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
