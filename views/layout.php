<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#101211"><meta name="description" content="Descubra novos mundos de pequenos criadores. PixelHub: jogos indie, homebrew e criatividade independente.">
    <title><?= e($title ?? 'Descobrir') ?> · PixelHub</title>
    <link rel="icon" href="<?= asset('images/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <?php if (in_array($page, ['admin', 'admin-setup'], true)): ?>
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <?php endif ?>
    <script src="<?= asset('js/app.js') ?>" defer></script>
</head>
<body>
<a class="skip-link" href="#main">Pular para o conteúdo</a>
<aside class="sidebar" id="sidebar">
    <a class="brand" href="<?= url() ?>" aria-label="PixelHub, início"><span class="brand-mark"><i></i><i></i><i></i><i></i></span>pixel<span>hub</span><b>β</b></a>
    <div class="nav-label">SEU PRÓXIMO UNIVERSO</div>
    <nav aria-label="Navegação principal">
        <?php foreach (
            [
                'discover' => ['compass', 'Descobrir'],
                'catalog' => ['grid', 'Explorar catálogo'],
                'collections' => ['spark', 'Coleções'],
            ]
            as $route => $nav
        ): ?>
            <a class="nav-link <?= $page === $route ? 'active' : '' ?>" href="<?= url(
    $route,
) ?>" <?= $page === $route ? 'aria-current="page"' : '' ?>><?=
icon($nav[0]) . e($nav[1]) . ($route === 'discover' ? '<span class="nav-dot"></span>' : '')
?></a>
        <?php endforeach; ?>
        <a class="nav-link <?= $page === 'random' ? 'active' : '' ?>" href="<?= url('random') ?>"><?= icon(
    'spark',
) ?>Surpreenda-me <span class="new-label">NOVO</span></a>
        <div class="nav-label secondary">SEU ESPAÇO</div>
        <?php foreach (
            [
                'library' => ['library', 'Minha biblioteca'],
                'favorites' => ['heart', 'Favoritos'],
                'profile' => ['user', 'Meu perfil'],
            ]
            as $route => $nav
        ): ?>
            <a class="nav-link <?= $page === $route ? 'active' : '' ?>" href="<?= url($route) ?>"><?=
icon($nav[0]) . e($nav[1])
?></a>
        <?php endforeach; ?>
        <a class="nav-link <?= $page === 'achievements' ? 'active' : '' ?>" href="<?= url(
    'achievements',
) ?>"><?= icon('trophy') ?>Conquistas</a>
        <div class="nav-divider"></div>
        <a class="nav-link <?= in_array($page, ['creator', 'editor']) ? 'active' : '' ?>" href="<?= url(
    'creator',
) ?>"><?= icon('code') ?>Espaço do criador<?= icon('arrow', 16) ?></a>
        <?php if (($currentUser['role'] ?? '') === 'admin'): ?><a class="nav-link" href="<?= url(
    'admin',
) ?>"><?= icon('shield') ?>Painel administrativo</a><?php endif; ?>
    </nav>
    <div class="sidebar-bottom">
        <div class="creator-note"><span class="small-orbit">✳</span><strong>Grandes ideias.<br>Pequenos estúdios.</strong><p>Tem um universo para compartilhar?</p><a href="<?= url(
            'creator',
        ) ?>">Publique seu jogo <?= icon('arrow', 16) ?></a></div>
        <a class="sidebar-foot" href="<?= url(
            'about',
        ) ?>"><span class="status-dot"></span> Feito para quem cria e joga</a>
        <span class="version">PIXELHUB · WEB BETA 0.1</span>
    </div>
</aside>
<div class="app-shell">
    <header class="topbar">
        <button class="icon-button mobile-menu" type="button" aria-label="Abrir menu" aria-controls="sidebar" aria-expanded="false"><?= icon(
            'menu',
        ) ?></button>
        <div class="breadcrumb">Seu ponto de encontro indie <span>/</span> <strong><?= e(
            in_array($page, ['discover', 'catalog']) ? 'Descobrir' : $title ?? 'PixelHub',
        ) ?></strong></div>
        <form class="header-search" method="get"><input type="hidden" name="page" value="catalog"><?= icon(
            'search',
            18,
        ) ?><input name="q" aria-label="Buscar jogos ou criadores" placeholder="Busque seu próximo jogo..." value="<?= e(
    is_string($_GET['q'] ?? null) ? $_GET['q'] : '',
) ?>"><kbd>/</kbd></form>
        <?php if ($currentUser): ?><a class="account-avatar" href="<?= url('profile') ?>" title="<?= e(
    $currentUser['name'],
) ?>"><?= e(
    mb_strtoupper(mb_substr($currentUser['name'], 0, 1)),
) ?></a><?php else: ?><a class="login-link" href="<?= url('login') ?>">Entrar <?= icon(
    'arrow',
    16,
) ?></a><?php endif; ?>
    </header>
    <main id="main">
        <?php if (isset($_SESSION['flash'])):

            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            ?><div class="flash <?= e($flash['type']) ?>" role="status"><?= icon(
    $flash['type'] === 'error' ? 'shield' : 'check',
) ?><span><?= e(
    $flash['message'],
) ?></span><button class="icon-button dismiss-flash" aria-label="Fechar aviso"><?= icon(
    'close',
    16,
) ?></button></div><?php
        endif; ?>
        <?php require ROOT . '/views/' . $view . '.php'; ?>
    </main>
    <footer class="footer"><span>© <?= date(
        'Y',
    ) ?> PixelHub <span class="footer-dot">·</span> Mundos independentes. Conexões de verdade.</span><div><a href="<?= url(
     'about',
 ) ?>">Sobre o projeto</a><a href="<?= url(
    'privacy',
) ?>">Privacidade</a><span class="beta-pill">BETA DEMONSTRATIVO</span></div></footer>
</div>
</body>
</html>
