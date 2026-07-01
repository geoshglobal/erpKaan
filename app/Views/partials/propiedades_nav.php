<?php
// $current: 'torres' | 'casas' | 'cajones'
$current ??= '';
$tabs = [
    'torres'  => ['Torres',  site_url('torres')],
    'casas'   => ['Casas',   site_url('casas')],
    'cajones' => ['Cajones', site_url('cajones')],
];
?>
<nav style="display:flex; gap:.4rem; margin-bottom:1.25rem; border-bottom:1px solid #e2e8f0; padding-bottom:.6rem;">
    <?php foreach ($tabs as $key => [$label, $url]): ?>
        <a href="<?= $url ?>"
           style="text-decoration:none; padding:.35rem .8rem; border-radius:8px; font-size:.88rem;
                  <?= $current === $key ? 'background:#2C6E52; color:#fff; font-weight:600;' : 'color:#3a4a41;' ?>">
            <?= esc($label) ?>
        </a>
    <?php endforeach; ?>
</nav>
