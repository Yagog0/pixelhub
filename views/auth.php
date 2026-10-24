<div class="auth-layout"><div class="auth-story"><span class="eyebrow">JOGAR É DESCOBRIR</span><h1>Todo grande universo<br>começa com uma<br><span class="lime">pequena ideia.</span></h1><p>Guarde suas descobertas, encontre novos criadores<br>e dê espaço para a sua próxima aventura.</p><img src="<?= asset(
    'images/orbit.svg',
) ?>" alt="Planeta lilás em um universo de pixels"></div><section class="panel auth-form"><span class="eyebrow">SEU ESPAÇO NA PIXELHUB</span><h2><?= $page ===
'login'
    ? 'Bom ter você por aqui.'
    : 'Vamos começar?' ?></h2><p class="muted"><?= $page === 'login'
    ? 'Entre para continuar sua jornada.'
    : 'Crie uma conta gratuita para jogar e publicar.' ?></p><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="<?= e(
    $page,
) ?>"><?php if (
    $page === 'register'
): ?><label>Como podemos chamar você?<input name="name" autocomplete="nickname" required minlength="2" maxlength="60" placeholder="Seu nome de jogador"></label><?php endif; ?><label>E-mail<input type="email" name="email" autocomplete="email" required maxlength="190" placeholder="voce@exemplo.com"></label><label>Senha<input type="password" name="password" autocomplete="<?= $page ===
'login'
    ? 'current-password'
    : 'new-password' ?>" required minlength="<?= $page === 'register'
    ? '10'
    : '1' ?>" maxlength="200" placeholder="<?= $page === 'register'
    ? 'Pelo menos 10 caracteres'
    : 'Sua senha' ?>"></label><?php if (
    $page === 'register'
): ?><label class="checkbox"><input type="checkbox" name="terms" required> <span>Li o <a href="<?= url(
    'privacy',
) ?>" target="_blank" rel="noopener">aviso de privacidade</a> deste beta demonstrativo.</span></label><?php endif; ?><button class="button primary full"><?= $page ===
'login'
    ? 'Entrar na minha conta'
    : 'Criar minha conta' ?> <?= icon('arrow', 18) ?></button></form><p class="auth-switch"><?= $page ===
'login'
    ? 'Sua primeira visita?'
    : 'Já faz parte?' ?> <a href="<?= url($page === 'login' ? 'register' : 'login') ?>"><?= $page === 'login'
    ? 'Crie uma conta'
    : 'Entre na sua conta' ?></a></p><div class="auth-foot"><?= icon(
    'shield',
    16,
) ?> Sua biblioteca é privada por padrão.</div></section></div>
