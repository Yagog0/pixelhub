<section class="page-heading"><div><div class="eyebrow"><span class="status-dot"></span> FORA DO ÓBVIO. DENTRO DO SEU MUNDO.</div><h1><?= e($settings['heading']) ?><br><?= e($settings['heading_line2']) ?><span class="lime">.</span></h1><p><?= e($settings['subtitle']) ?></p></div><a class="text-link heading-link" href="<?= url(
    'catalog',
) ?>">Explorar todos os jogos <?= icon('arrow', 17) ?></a></section>
<?php
// Destaques editoriais navegáveis sem depender de JavaScript ou rotação automática.
$features = $settings['features'];
$featureIndex = max(0, min(2, (int) ($_GET['feature'] ?? $settings['default_feature'])));
$feature = $features[$featureIndex];
?>
<section class="hero" id="destaque" aria-label="Coleção em destaque">
    <img class="hero-art" src="<?= asset(
        'images/' . $feature['art'] . '.svg',
    ) ?>" alt="Paisagem em pixel art da coleção <?= e($feature['collection']) ?>">
    <div class="hero-shade"></div>
    <div class="hero-content">
        <span class="hero-tag"><?= icon('spark', 14) ?> ESCOLHAS PIXELHUB <span>0<?= $featureIndex +
     1 ?> / 03</span></span>
        <span class="hero-kicker"><?= e($feature['kicker']) ?></span>
        <h2><?= e($feature['title'][0]) ?><br><?= e($feature['title'][1]) ?></h2>
        <p><?= e($feature['description'][0]) ?><br><?= e($feature['description'][1]) ?></p>
        <a class="button primary" href="<?= url('catalog', [
            'collection' => $feature['collection'],
        ]) ?>">Encontre sua próxima aventura <?= icon('arrow', 18) ?></a>
        <div class="hero-caption"><span class="mini-avatars"><i>D</i><i>L</i><i>M</i></span><span>Uma coleção feita de ideias independentes</span></div>
    </div>
    <div class="hero-bottom"><span>COLEÇÃO #00<?= $featureIndex + 1 ?> <b><?= e(
     $feature['collection'],
 ) ?></b></span><nav class="hero-progress" aria-label="Escolher destaque"><?php foreach (
    $features
    as $i => $item
): ?><a class="<?= $featureIndex === $i ? 'current' : '' ?>" href="<?= url('discover', [
    'feature' => $i,
]) ?>#destaque" aria-label="Ver destaque: <?= e($item['collection']) ?>" <?= $featureIndex === $i
    ? 'aria-current="true"'
    : '' ?>></a><?php endforeach; ?></nav></div>
</section>
<div class="values-strip"><span><?= icon('code', 17) ?> Criatividade independente</span><span><?= icon(
     'heart',
     17,
 ) ?> Feito por pequenos criadores</span><span><?= icon(
     'compass',
     17,
 ) ?> Novos mundos para explorar</span><a href="<?= url('about') ?>">Conheça a PixelHub <?= icon(
    'arrow',
    15,
) ?></a></div>
<section class="discovery-section"><div class="section-heading"><div><span class="eyebrow">VALE O SEU PRÓXIMO CLIQUE</span><h2><?= e($settings['section_title']) ?> <span class="lime">✦</span></h2></div><a class="text-link" href="<?= url(
    'catalog',
) ?>">Ver catálogo <?= icon('arrow', 17) ?></a></div>
    <div class="filter-row"><div class="filter-chips"><?php foreach (
        [
            '' => 'Para você',
            'Aventura' => 'Aventura',
            'Puzzle' => 'Puzzle',
            'Plataforma' => 'Plataforma',
            'RPG' => 'RPG',
        ]
        as $value => $label
    ): ?><a class="chip <?= ($filters['genre'] ?? '') === $value ? 'selected' : '' ?>" href="<?= url(
    'discover',
    $value ? ['genre' => $value] : [],
) ?>"><?=
($value === '' ? icon('spark', 14) : '') . e($label)
?></a><?php endforeach; ?><a class="chip <?= ($filters['type'] ?? '') === 'Homebrew'
    ? 'selected'
    : '' ?>" href="<?= url('discover', [
    'type' => 'Homebrew',
]) ?>">Homebrew</a></div><span class="muted small"><?= count($games) ?> mundos esperando por você</span></div>
    <div class="game-grid"><?php foreach (array_slice($games, 0, (int)$settings['home_count']) as $game) {
        require ROOT . '/views/partials/card.php';
    } ?></div>
    <?php if (
        !$games
    ): ?><div class="empty-state"><h3>Nenhum mundo por aqui ainda.</h3><a href="<?= url() ?>">Limpar filtros</a></div><?php endif; ?>
</section>
<section class="bottom-promos"><a class="collection-promo" href="<?= url('catalog', [
    'collection' => 'Feitos de pixels',
]) ?>"><img src="<?= asset(
    'images/circuit.svg',
) ?>" alt="Pista neon em pixel art" loading="lazy"><div><span class="eyebrow">UM POUCO DE NOSTALGIA</span><h3>Pixels de ontem.<br>Ideias de amanhã.</h3><span>Explore o homebrew original <?= icon(
    'arrow',
    16,
) ?></span></div></a><div class="publish-promo"><span class="code-square"><?= icon(
    'code',
    30,
) ?></span><div><span class="eyebrow">DO SEU MUNDO PARA O MUNDO</span><h3>Você cria. A gente conecta.</h3><p>Dê um lugar para o seu próximo jogo.<br>Seu espaço de criador começa aqui.</p><a class="text-link lime" href="<?= url(
    'creator',
) ?>">Conhecer o espaço do criador <?= icon('arrow', 17) ?></a></div></div></section>
<p class="demo-note">Catálogo demonstrativo: títulos e estúdios fictícios. Órbita de Papel inclui um minijogo original jogável.</p>
