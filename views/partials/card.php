<article class="game-card">
    <a class="card-art" href="<?= url('game', ['id' => $game['id']]) ?>"><img src="<?= asset(
    'images/' . $game['art'] . '.svg',
) ?>" alt="Paisagem ilustrada de <?= e($game['title']) ?>" loading="lazy"><span class="art-type"><?= e(
    $game['type'],
) ?></span><?php if ($game['playable']): ?><span class="play-badge"><?= icon(
    'play',
    12,
) ?> JOGUE AGORA</span><?php endif; ?></a>
    <div class="card-body"><div class="card-title"><a href="<?= url('game', ['id' => $game['id']]) ?>"><?= e(
    $game['title'],
) ?></a><span title="<?= e($game['platform']) ?>"><?= icon(
    $game['platform'] === 'Windows' ? 'windows' : 'compass',
    15,
) ?></span></div><p class="card-creator"><?= e($game['creator']) ?></p><div class="card-bottom"><span><?= e(
    $game['genre'],
) ?> <span class="tiny-dot">·</span> <?= e($game['language']) ?></span><strong><?= $game['type'] === 'Demo'
    ? 'Demo grátis'
    : 'Gratuito' ?></strong></div></div>
</article>
