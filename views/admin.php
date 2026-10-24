<?php // Cada aba tem seu próprio arquivo para facilitar a manutenção.
$tabs = App\Controllers\AdminController::TABS; ?>
<section class="page-heading admin-heading">
    <div><span class="eyebrow">PIXELHUB / ADMINISTRAÇÃO</span><h1>Você no controle<span class="lime">.</span></h1><p>Do próximo jogo que chega ao próximo mundo que alguém descobre.</p></div>
    <a class="button secondary" href="<?= url() ?>" target="_blank" rel="noopener">Ver site <?= icon('arrow',17) ?></a>
</section>
<div class="admin-status"><span class="status-dot"></span> Seleção automática ativa · Publicação com revisão manual <span class="admin-updated">Consultado às <?= date('H:i') ?> <a href="<?= url('admin',['tab'=>$tab]) ?>">Atualizar</a></span></div>
<nav class="admin-tabs" aria-label="Seções administrativas">
<?php foreach($tabs as $key=>$label): ?>
    <a class="<?= $tab===$key?'selected':'' ?>" href="<?= url('admin',['tab'=>$key]) ?>" <?= $tab===$key?'aria-current="page"':'' ?>><?= e($label) ?><?php if($key==='queue' && $stats['pending']): ?><span><?= (int)$stats['pending'] ?></span><?php endif ?><?php if($key==='reports' && $stats['reports']): ?><span><?= (int)$stats['reports'] ?></span><?php endif ?></a>
<?php endforeach ?>
</nav>
<?php require ROOT . '/views/admin/' . $tab . '.php'; ?>
<?php if($pages>1): ?>
<nav class="admin-pagination" aria-label="Paginação">
    <?php if($number>1): ?><a class="button secondary" href="<?= url('admin',['tab'=>$tab,'n'=>$number-1,'q'=>$query,'status'=>$status]) ?>">← Anterior</a><?php endif ?>
    <span>Página <?= $number ?> de <?= $pages ?> · <?= $total ?> registros</span>
    <?php if($number<$pages): ?><a class="button secondary" href="<?= url('admin',['tab'=>$tab,'n'=>$number+1,'q'=>$query,'status'=>$status]) ?>">Próxima →</a><?php endif ?>
</nav>
<?php endif ?>
