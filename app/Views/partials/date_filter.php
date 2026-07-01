<?php
/**
 * Compact date-range filter. Params: $range (['desde','hasta']) and $action (URL).
 */
$action = $action ?? current_url();
?>
<form class="filters" method="get" action="<?= esc($action, 'attr') ?>">
    <div class="field"><label>Desde</label>
        <input type="date" name="desde" value="<?= esc($range['desde'] ?? '', 'attr') ?>"></div>
    <div class="field"><label>Hasta</label>
        <input type="date" name="hasta" value="<?= esc($range['hasta'] ?? '', 'attr') ?>"></div>
    <button type="submit" class="btn">Filtrar</button>
    <a class="btn secondary" href="<?= esc($action, 'attr') ?>">Limpiar</a>
</form>
<p class="muted" style="font-size:.8rem; margin:-.5rem 0 1rem;">Mostrando <strong><?= esc($range['desde'] ?? '') ?></strong> a <strong><?= esc($range['hasta'] ?? '') ?></strong> (por defecto, últimos 15 días).</p>
