<section class="page-heading"><div><span class="eyebrow">DO PRIMEIRO PIXEL À PRÓXIMA VERSÃO</span><h1>Espaço do criador<span class="lime">.</span></h1><p>Suas ideias merecem encontrar quem vai jogar.</p></div><a class="button primary" href="<?= url(
    'editor',
) ?>">+ Novo projeto</a></section>
<div class="stats-grid"><div class="panel stat"><span>Seus projetos</span><strong><?= count(
    $projects,
) ?><small> / 3 no beta</small></strong></div><div class="panel stat"><span>No catálogo</span><strong><?= count(
    array_filter($projects, fn($p) => $p['status'] === 'published'),
) ?></strong></div><div class="panel stat"><span>Adições à biblioteca</span><strong><?= array_sum(
    array_column($projects, 'additions'),
) ?></strong></div></div>
<div class="notice"><?= icon(
    'shield',
    22,
) ?><div><strong>Da sua ideia ao catálogo, com cuidado.</strong><p>Salve os detalhes, anexe uma build ZIP e documente os direitos. A publicação depende de revisão manual. Limite deste protótipo: 20 MB por build.</p></div></div>
<?php if (!$projects): ?><div class="empty-state panel"><?= icon(
    'code',
    48,
) ?><h2>Seu primeiro mundo começa aqui</h2><p>Prepare a página do seu jogo no seu tempo. Rascunhos ficam privados.</p><a class="button primary" href="<?= url(
    'editor',
) ?>">Criar meu primeiro projeto <?= icon(
    'arrow',
    18,
) ?></a></div><?php else: ?><div class="project-list"><?php foreach (
    $projects
    as $project
): ?><article class="panel project-row"><img src="<?= asset(
    'images/' . $project['art'] . '.svg',
) ?>" alt=""><div><span class="badge <?= e($project['status']) ?>"><?= e(
    status_label($project['status']),
) ?></span><h2><?= e($project['title']) ?></h2><p class="muted">v<?= e($project['version']) ?> · <?= e(
     $project['platform'],
 ) ?> · <?= $project['additions'] ?> na biblioteca</p><?php if (
     $project['review_note']
 ): ?><p class="small"><?= e(
    $project['review_note'],
) ?></p><?php endif; ?></div><div class="actions"><a class="button secondary" href="<?= url('game', [
    'id' => $project['id'],
]) ?>">Prévia</a><?php if (
    in_array($project['status'], ['draft', 'rejected'])
): ?><a class="button secondary" href="<?= url('editor', [
    'id' => $project['id'],
]) ?>">Editar</a><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="submit"><input type="hidden" name="id" value="<?= $project[
    'id'
] ?>"><button class="button primary">Enviar para revisão</button></form><?php endif; ?></div></article><?php endforeach; ?></div><?php endif; ?>
<p class="demo-note">As métricas contam adições reais à biblioteca deste ambiente. Downloads não são apresentados como instalações.</p>
