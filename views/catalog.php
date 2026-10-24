<section class="page-heading"><div><span class="eyebrow">CURIOSIDADE É UM ÓTIMO PONTO DE PARTIDA</span><h1>Explore novos mundos<span class="lime">.</span></h1><p>Uma próxima aventura para cada jeito de jogar.</p></div></section>
<form class="catalog-filters panel" method="get"><input type="hidden" name="page" value="catalog"><label>Buscar<input name="q" placeholder="Nome do jogo ou criador" value="<?= e(
    $filters['q'] ?? '',
) ?>"></label><?php foreach (
    [
        'genre' => ['Gênero', ['Aventura', 'Puzzle', 'Plataforma', 'RPG', 'Corrida', 'Exploração']],
        'type' => ['Tipo', ['Jogo completo', 'Demo', 'Homebrew']],
        'platform' => ['Plataforma', ['Windows', 'Linux', 'Navegador']],
        'collection' => ['Coleção', ['Para jogar em uma tarde', 'Feitos de pixels', 'Novos horizontes']],
    ]
    as $key => $option
): ?><label><?= $option[0] ?><select name="<?= $key ?>"><option value="">Todos</option><?php foreach (
    $option[1]
    as $value
): ?><option <?= ($filters[$key] ?? '') === $value ? 'selected' : '' ?>><?= e(
    $value,
) ?></option><?php endforeach; ?></select></label><?php endforeach; ?><label>Ordenar<select name="sort"><option value="">Seleção editorial</option><option value="new" <?= ($filters[
    'sort'
] ??
    '') ===
'new'
    ? 'selected'
    : '' ?>>Mais recentes</option><option value="name" <?= ($filters['sort'] ?? '') === 'name'
    ? 'selected'
    : '' ?>>Nome A–Z</option></select></label><button class="button primary">Filtrar <?= icon(
    'search',
    16,
) ?></button></form>
<div class="section-heading"><p class="muted"><?= count($games) ?> jogos encontrados<?= !empty(
     $filters['collection']
 )
     ? ' · ' . e($filters['collection'])
     : '' ?></p><a class="text-link" href="<?= url(
    'catalog',
) ?>">Limpar filtros</a></div><div class="game-grid"><?php foreach ($games as $game) {
    require ROOT . '/views/partials/card.php';
} ?></div>
<?php if (!$games): ?><div class="empty-state"><?= icon(
    'compass',
    40,
) ?><h2>Nenhum jogo encontrado</h2><p>Experimente outro nome ou remova alguns filtros.</p><a class="button secondary" href="<?= url(
    'catalog',
) ?>">Explorar tudo</a></div><?php endif; ?>
<p class="demo-note">Os projetos marcados como demonstração são fictícios e não representam jogos comerciais disponíveis.</p>
