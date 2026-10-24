<?php $unlocked = count(array_filter($achievements, fn($a) => $a['unlocked_at'] !== null)); ?>
<section class="page-heading"><div><span class="eyebrow">CADA DESCOBERTA CONTA</span><h1>Pequenas vitórias.<br>Grandes histórias<span class="lime">.</span></h1><p>Sua jornada, um novo marco de cada vez. Só você vê suas conquistas.</p></div><div class="achievement-total"><?= icon(
    'trophy',
    30,
) ?><strong><?= $unlocked ?><small> / <?= count(
    $achievements,
) ?></small></strong><span>conquistas desbloqueadas</span></div></section>
<div class="achievement-grid"><?php foreach ($achievements as $achievement):
    $secret =
        $achievement['secret'] &&
        !$achievement['unlocked_at']; ?><article class="panel achievement-card <?= $achievement['unlocked_at']
    ? 'unlocked'
    : 'locked' ?>"><div class="achievement-icon"><?= icon(
    $achievement['unlocked_at'] ? 'trophy' : 'shield',
    30,
) ?></div><div><span class="eyebrow"><?= e(
    $achievement['game_title'] ?? 'JORNADA PIXELHUB',
) ?></span><h2><?= $secret ? 'Conquista secreta' : e($achievement['title']) ?></h2><p><?= $secret
    ? 'Continue explorando este jogo para descobrir.'
    : e($achievement['description']) ?></p><span class="achievement-date"><?= $achievement['unlocked_at']
    ? 'Desbloqueada em ' . date('d/m/Y', strtotime($achievement['unlocked_at']))
    : 'Ainda há uma história para viver' ?></span><?php if (
    $achievement['source'] &&
    $achievement['source'] !== 'platform'
): ?><span class="integration-label">Informada pelo jogo · sem validação competitiva</span><?php endif; ?></div></article><?php
endforeach; ?></div>
<div class="notice"><?= icon(
    'compass',
    23,
) ?><div><strong>Quer começar sua coleção de conquistas?</strong><p>Guarde jogos na biblioteca, escolha um favorito ou explore as estrelas em Órbita de Papel.</p><a class="text-link lime" href="<?= url(
    'play',
) ?>">Começar uma aventura <?= icon('arrow', 16) ?></a></div></div>
