<a class="back-link" href="<?= url('game', [
    'id' => 1,
]) ?>">← Sobre o jogo</a><section class="page-heading"><div><span class="eyebrow">UM MINIJOGO ORIGINAL PIXELHUB</span><h1>Órbita de Papel<span class="lime">.</span></h1><p>Recolha estrelas, desvie dos meteoros e encontre seu lugar no universo.</p></div><span class="badge">NAVEGADOR · GRATUITO</span></section><section class="game-player panel"><div class="player-toolbar"><span>ESTRELAS <strong id="game-score">0</strong></span><span>RECORDE LOCAL <strong id="game-best">0</strong></span><button id="game-pause" class="button secondary" type="button">Pausar</button></div><canvas id="orbit-game" width="960" height="540" tabindex="0" data-csrf="<?= $currentUser
    ? e(csrf())
    : '' ?>" aria-label="Órbita de Papel: mova a nave com as setas ou A e D; desvie dos meteoros e recolha estrelas."></canvas><div class="player-controls"><button class="button secondary" id="move-left" aria-label="Mover para a esquerda">←</button><p id="game-status" role="status">Setas ou A/D para mover · espaço para pausar</p><button class="button primary" id="game-start">Começar aventura <?= icon(
    'play',
    16,
) ?></button><button class="button secondary" id="move-right" aria-label="Mover para a direita">→</button></div></section><div id="achievement-toast" class="achievement-toast" role="status" hidden></div><p class="demo-note">Recorde salvo apenas neste navegador. <?= $currentUser
    ? 'Conquistas são informadas por este minijogo à sua conta, sem validação competitiva.'
    : 'Entre na sua conta para guardar conquistas.' ?></p><script src="<?= asset(
    'js/game.js',
) ?>" defer></script>
