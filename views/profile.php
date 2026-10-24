<section class="page-heading"><div><span class="eyebrow">SEU CANTINHO POR AQUI</span><h1>Olá, <?= e(
    $currentUser['name'],
) ?><span class="lime">.</span></h1><p>Uma identidade sua. Uma biblioteca só sua.</p></div></section><div class="profile-grid"><section class="panel"><h2>Seu perfil</h2><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="profile"><label>Nome de exibição<input name="name" required minlength="2" maxlength="60" value="<?= e(
    $currentUser['name'],
) ?>"></label><label>Um pouco sobre você<textarea name="bio" maxlength="400" rows="4" placeholder="Que mundos você gosta de explorar?"><?= e(
    $currentUser['bio'],
) ?></textarea></label><label>E-mail<input value="<?= e(
    $currentUser['email'],
) ?>" disabled></label><button class="button primary">Salvar alterações</button></form></section><div><section class="panel"><span class="eyebrow">SUAS DESCOBERTAS</span><h2><?= $count ?> <?= $count ===
 1
     ? 'jogo guardado'
     : 'jogos guardados' ?></h2><p class="muted">Biblioteca e favoritos são privados. Este protótipo não publica perfis nem rastreia tempo de jogo.</p><a class="button secondary" href="<?= url(
    'library',
) ?>">Abrir minha biblioteca</a></section><section class="panel"><h3>Você no controle</h3><p class="muted">Exporte os dados da sua conta em um arquivo JSON.</p><a class="text-link lime" href="<?= url(
    'export',
) ?>"><?= icon(
    'download',
    17,
) ?> Exportar meus dados</a><form class="logout-form" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="logout"><button class="button secondary">Sair da conta</button></form></section></div></div>
