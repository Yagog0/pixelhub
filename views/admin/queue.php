<div class="notice"><?= icon('shield',22) ?><div>Novas submissões chegam automaticamente. Publicar exige revisão de direitos e de segurança. Os ZIPs ficam privados até a aprovação; não há análise antivírus automática.</div></div>
<?php require __DIR__.'/filters.php'; ?>
<?php if(!$rows): ?><div class="panel admin-empty"><h2>Nenhum envio neste filtro</h2><p>Assim que um criador cadastrar um projeto, você poderá acompanhá-lo aqui.</p></div><?php endif ?>
<?php foreach($rows as $project): ?>
<article class="panel admin-project">
    <header class="admin-project-header"><img src="<?= asset('images/'.$project['art'].'.svg') ?>" alt=""><div><span class="badge <?= e($project['status']) ?>"><?= e(status_label($project['status'])) ?></span><h2><?= e($project['title']) ?></h2><p><?= e($project['creator']) ?> · <?= e($project['email']) ?> · v<?= e($project['version']) ?></p></div><a class="button secondary" href="<?= url('game',['id'=>$project['id']]) ?>" target="_blank" rel="noopener">Abrir prévia →</a></header>
    <details><summary>Ver direitos e arquivo da build</summary><div class="admin-review-data"><div><h3>Licença</h3><p><?= e($project['license']?:'Não informada') ?></p><h3>Evidências privadas</h3><p><?= nl2br(e($project['rights']?:'Não informadas')) ?></p></div><div><h3>Build</h3><p><?= $project['build_path']?number_format($project['build_size']/1048576,2,',','.').' MB':'Nenhum arquivo enviado' ?></p><code class="hash-code"><?= e($project['build_hash']??'') ?></code><?php if($project['build_path']): ?><a class="button secondary" href="<?= url('download',['id'=>$project['id'],'review'=>1]) ?>">Baixar para revisão isolada</a><?php endif ?></div></div></details>
    <?php if($project['review_note']): ?><p class="admin-review-note"><strong>Última decisão:</strong> <?= e($project['review_note']) ?></p><?php endif ?>
    <?php if(in_array($project['status'],['pending','published'],true)) require __DIR__.'/review-form.php'; ?>
</article>
<?php endforeach ?>
