<div class="notice"><?= icon('spark',22) ?><div><strong>Você organiza. A seleção automática completa.</strong><p>Destaques vêm primeiro; a menor prioridade numérica aparece antes. Os demais seguem a ordem da página inicial. Ocultar mantém a página por link e a biblioteca; para bloquear acesso e downloads, use Retirar.</p></div></div>
<?php require __DIR__.'/filters.php'; ?>
<?php if(!$rows): ?><div class="panel admin-empty"><h2>Nenhum projeto encontrado</h2><p>Experimente limpar os filtros.</p></div><?php endif ?>
<div class="admin-catalog-grid">
<?php foreach($rows as $project): ?>
<article class="panel admin-catalog-card">
    <div class="admin-catalog-cover"><img src="<?= asset('images/'.$project['art'].'.svg') ?>" alt=""><span class="badge <?= e($project['status']) ?>"><?= e(status_label($project['status'])) ?></span></div>
    <div class="admin-catalog-body"><h2><?= e($project['title']) ?></h2><p class="small muted"><?= e($project['creator']) ?><?= $project['demo']?' · Fictício / demonstração':'' ?></p>
    <?php if($project['hidden']): ?><span class="badge withdrawn">Oculto das vitrines</span><?php endif ?><?php if($project['featured']): ?><span class="badge">Destaque manual</span><?php endif ?>
    <form method="post" action="<?= url('admin') ?>">
        <?= csrf_field() ?><input type="hidden" name="action" value="admin_display"><input type="hidden" name="id" value="<?= (int)$project['id'] ?>">
        <div class="form-grid"><label>Coleção<select name="collection"><?php foreach(App\Models\SiteSettings::COLLECTIONS as $collection): ?><option <?= $project['display_collection']===$collection?'selected':'' ?>><?= e($collection) ?></option><?php endforeach ?></select></label><label>Prioridade<input type="number" name="position" min="0" max="9999" required value="<?= (int)$project['position'] ?>"></label></div>
        <label class="checkbox"><input type="checkbox" name="featured" <?= $project['featured']?'checked':'' ?>><span>Priorizar como destaque na home e seleção editorial.</span></label>
        <label class="checkbox"><input type="checkbox" name="hidden" <?= $project['hidden']?'checked':'' ?>><span>Ocultar de vitrines, buscas, coleções e sorteio.</span></label>
        <button class="button primary">Salvar exibição</button>
    </form>
    <div class="admin-card-actions"><form method="post" action="<?= url('admin') ?>"><?= csrf_field() ?><input type="hidden" name="action" value="admin_auto"><input type="hidden" name="id" value="<?= (int)$project['id'] ?>"><button class="button secondary">Voltar ao automático</button></form><a class="text-link" href="<?= url('game',['id'=>$project['id']]) ?>">Ver página →</a></div>
    <?php if($project['status']==='published'): ?><details><summary>Retirar publicação</summary><?php require __DIR__.'/review-form.php'; ?></details><?php else: ?><p class="small muted">A curadoria entra em vigor quando o projeto for publicado.</p><?php endif ?>
    </div>
</article>
<?php endforeach ?>
</div>
