<?php
/**
 * Kaan pagination template (CI4 PagerRenderer). Register as 'kaan' in Config\Pager.
 * @var CodeIgniter\Pager\PagerRenderer $pager
 */
$pager->setSurroundCount(1);
?>
<?php if ($pager->getPageCount() > 1): ?>
<nav class="pager" aria-label="Paginación">
    <?php if ($pager->hasPrevious()): ?>
        <a class="pg" href="<?= esc($pager->getPrevious(), 'url') ?>" aria-label="Anterior">‹</a>
    <?php endif; ?>

    <?php foreach ($pager->links() as $link): ?>
        <a class="pg <?= $link['active'] ? 'active' : '' ?>" href="<?= esc($link['uri'], 'url') ?>"><?= esc($link['title']) ?></a>
    <?php endforeach; ?>

    <?php if ($pager->hasNext()): ?>
        <a class="pg" href="<?= esc($pager->getNext(), 'url') ?>" aria-label="Siguiente">›</a>
    <?php endif; ?>
</nav>
<?php endif; ?>
