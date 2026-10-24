<!-- Busca paginada sem carregar todos os registros na página. -->
<form class="admin-filters panel" method="get" action="<?= url('admin') ?>">
    <input type="hidden" name="tab" value="<?= e($tab) ?>">
    <label>Buscar<input name="q" maxlength="100" value="<?= e($query) ?>" placeholder="<?= $tab==='users'?'Nome ou e-mail':'Jogo ou criador' ?>"></label>
    <?php if($tab!=='users'): ?><label>Status<select name="status"><option value="">Todos os status</option><?php foreach(['pending','draft','published','rejected','withdrawn'] as $option): ?><option value="<?= $option ?>" <?= $status===$option?'selected':'' ?>><?= e(status_label($option)) ?></option><?php endforeach ?></select></label><?php endif ?>
    <button class="button primary">Buscar <?= icon('search',16) ?></button><a class="text-link" href="<?= url('admin',['tab'=>$tab]) ?>">Limpar</a>
</form><p class="small muted"><?= $total ?> registros encontrados</p>
