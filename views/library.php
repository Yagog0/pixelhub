<section class="page-heading"><div><span class="eyebrow">UM CANTINHO DOS SEUS UNIVERSOS</span><h1><?= e(
    $title,
) ?><span class="lime">.</span></h1><p>Suas descobertas, guardadas para a próxima aventura. Só você pode vê-las.</p></div><a class="button secondary" href="<?= url(
    'catalog',
) ?>">Descobrir mais <?= icon('arrow', 17) ?></a></section>
<div class="filter-chips library-tabs"><a class="chip <?= $page === 'library'
    ? 'selected'
    : '' ?>" href="<?= url('library') ?>">Todos os jogos</a><a class="chip <?= $page === 'favorites'
    ? 'selected'
    : '' ?>" href="<?= url('favorites') ?>"><?= icon('heart', 15) ?> Favoritos</a></div>
<?php if (!$games): ?><div class="empty-state panel"><?= icon(
    'library',
    48,
) ?><h2>Sua próxima história está esperando</h2><p>Adicione um jogo do catálogo e encontre suas descobertas por aqui.</p><a class="button primary" href="<?= url(
    'catalog',
) ?>">Encontrar meu primeiro jogo <?= icon(
    'arrow',
    18,
) ?></a></div><?php else: ?><div class="library-grid"><?php foreach (
    $games
    as $game
): ?><article class="library-card panel"><a href="<?= url('game', [
    'id' => $game['id'],
]) ?>"><img src="<?= asset('images/' . $game['art'] . '.svg') ?>" alt="<?= e(
    $game['title'],
) ?>"></a><div class="library-info"><span class="eyebrow"><?= e($game['genre']) ?></span><h2><?= e(
    $game['title'],
) ?></h2><p class="muted"><?= e($game['creator']) ?> · v<?= e(
     $game['version'],
 ) ?></p><p class="small"><?= $game['status'] !== 'published'
    ? 'Retirado do catálogo · novos downloads indisponíveis'
    : ($game['playable']
        ? 'Pronto para jogar no navegador'
        : ($game['build_path']
            ? 'Build disponível para download'
            : 'Demonstração · sem build disponível')) ?></p><div class="actions"><?php if (
    $game['status'] === 'published'
): ?><a class="button primary" href="<?= $game['playable']
    ? url('play')
    : ($game['build_path']
        ? url('download', ['id' => $game['id']])
        : url('game', ['id' => $game['id']])) ?>"><?= icon(
    $game['playable'] ? 'play' : ($game['build_path'] ? 'download' : 'arrow'),
    16,
) ?> <?= $game['playable']
     ? 'Jogar'
     : ($game['build_path']
         ? 'Baixar'
         : 'Ver projeto') ?></a><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="favorite"><input type="hidden" name="id" value="<?= $game[
    'id'
] ?>"><button class="button secondary <?= $game['favorite'] ? 'lime' : '' ?>" aria-label="<?= $game[
    'favorite'
]
    ? 'Remover dos favoritos'
    : 'Marcar como favorito' ?>"><?= icon(
    'heart',
    18,
) ?></button></form><?php endif; ?><form method="post" data-confirm="Remover este jogo da sua biblioteca?"><?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="<?= $game[
    'id'
] ?>"><button class="icon-button muted" aria-label="Remover da biblioteca"><?= icon(
    'close',
    18,
) ?></button></form></div></div></article><?php endforeach; ?></div><?php endif; ?>
