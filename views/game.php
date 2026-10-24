<a class="back-link" href="<?= url('catalog') ?>">← Voltar ao catálogo</a>
<?php if ($game['status'] !== 'published'): ?><div class="notice">Prévia privada · <?= e(
    status_label($game['status']),
) ?> · <?= e($game['review_note']) ?></div><?php endif; ?>
<div class="game-detail"><div><div class="detail-art"><img src="<?= asset(
    'images/' . $game['art'] . '.svg',
) ?>" alt="Ilustração de <?= e($game['title']) ?>"><span class="art-type"><?= e(
    $game['type'],
) ?></span></div><section class="panel prose"><span class="eyebrow">UM UNIVERSO PARA CHAMAR DE SEU</span><h2>Sobre o jogo</h2><p><?= nl2br(
    e($game['description']),
) ?></p><h3>Como jogar</h3><p><?= nl2br(e($game['controls'])) ?></p><h3>Requisitos</h3><p><?= nl2br(
    e($game['requirements']),
) ?></p><h3>O que mudou · v<?= e($game['version']) ?></h3><p><?= nl2br(
    e($game['changelog']),
) ?></p><h3>Conteúdo e licença</h3><p><?= e($game['content_notice']) ?></p><p><?= nl2br(
    e($game['license']),
) ?></p></section></div>
<aside class="detail-sidebar panel"><span class="eyebrow"><?= e($game['genre']) ?> / <?= e(
     $game['type'],
 ) ?></span><h1><?= e($game['title']) ?></h1><p class="muted">por <strong><?= e(
    $game['creator'],
) ?></strong></p><p><?= e(
    $game['summary'],
) ?></p><div class="detail-price">Gratuito <span>para explorar</span></div>
<?php if (
    $game['status'] === 'published'
): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="library"><input type="hidden" name="id" value="<?= $game[
    'id'
] ?>"><button class="button primary full"><?= icon($entry ? 'check' : 'library', 18) ?> <?= $entry
     ? 'Na biblioteca · abrir'
     : 'Adicionar à biblioteca' ?></button></form><?php endif; ?>
<?php if (
    $game['playable'] &&
    $game['status'] === 'published'
): ?><a class="button secondary full" href="<?= url('play') ?>"><?= icon(
    'play',
    17,
) ?> Jogar no navegador</a><?php elseif (
    $game['build_path'] &&
    $entry &&
    $game['status'] === 'published'
): ?><a class="button secondary full" href="<?= url('download', ['id' => $game['id']]) ?>"><?= icon(
    'download',
    17,
) ?> Baixar ZIP · <?= number_format($game['build_size'] / 1048576, 1, ',', '.') ?> MB</a><?php endif; ?>
<dl class="specs"><div><dt>Plataforma</dt><dd><?= e(
    $game['platform'],
) ?></dd></div><div><dt>Idioma</dt><dd><?= e(
    $game['language'],
) ?></dd></div><div><dt>Uma boa pausa de</dt><dd><?= e(
    $game['duration'],
) ?></dd></div><div><dt>Versão</dt><dd><?= e($game['version']) ?></dd></div></dl>
<?php if ($game['demo']): ?><div class="notice small">Projeto demonstrativo. <?= $game['playable']
    ? 'O minijogo original pode ser jogado neste navegador.'
    : 'A arte e os metadados são ilustrativos. Nenhuma build está disponível.' ?></div><?php elseif (
    $game['build_hash']
): ?><details class="hash"><summary>Integridade do arquivo · SHA-256</summary><code><?= e(
    $game['build_hash'],
) ?></code></details><?php endif; ?>
<?php if (
    $game['status'] === 'published'
): ?><details class="report"><summary>Reportar um problema</summary><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="report"><input type="hidden" name="id" value="<?= $game[
    'id'
] ?>"><label>O que aconteceu?<textarea name="reason" required minlength="10" maxlength="1500" rows="4" placeholder="Descreva o problema ou a denúncia."></textarea></label><button class="button secondary full">Enviar relato</button><p class="small muted">É necessário entrar na sua conta.</p></form></details><?php endif; ?></aside></div>
<?php
$definitions = App\Core\Database::all(
    'SELECT a.*, u.created_at AS unlocked_at FROM achievements a LEFT JOIN unlocks u ON u.achievement_id=a.id AND u.user_id=? WHERE a.project_id=?',
    [$currentUser['id'] ?? 0, $game['id']],
);
if ($definitions): ?><section class="panel"><div class="section-heading"><h2><?= icon(
    'trophy',
    23,
) ?> Conquistas deste jogo</h2><a class="text-link lime" href="<?= url(
     'achievements',
 ) ?>">Minha jornada <?= icon('arrow', 16) ?></a></div><div class="game-achievements"><?php foreach (
    $definitions
    as $definition
): ?><div class="mini-achievement"><?= icon(
    $definition['unlocked_at'] ? 'trophy' : 'shield',
    24,
) ?><strong><?= $definition['secret'] && !$definition['unlocked_at']
    ? 'Conquista secreta'
    : e($definition['title']) ?></strong><p><?= $definition['secret'] && !$definition['unlocked_at']
    ? 'Continue explorando para descobrir.'
    : e($definition['description']) ?></p></div><?php endforeach; ?></div><?php if (
    $entry &&
    !$game['playable'] &&
    $game['status'] === 'published'
): ?><details><summary>Conectar conquistas ao meu jogo</summary><p class="small muted">Gere uma chave pessoal válida por uma hora. Ela permite apenas registrar conquistas deste jogo na sua conta. Uma nova chave revoga a anterior.</p><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="game_token"><input type="hidden" name="id" value="<?= $game[
    'id'
] ?>"><button class="button secondary">Gerar chave de integração</button></form><?php if (
    ($_SESSION['new_game_token']['id'] ?? 0) ===
    $game['id']
): ?><p class="small">Copie agora. A chave só aparece uma vez:</p><code class="hash-code"><?= e(
    $_SESSION['new_game_token']['token'],
) ?></code><?php unset($_SESSION['new_game_token']);endif; ?></details><?php endif; ?></section><?php endif;
 ?>
