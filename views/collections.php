<section class="page-heading"><div><span class="eyebrow">ENCONTROS QUE FAZEM SENTIDO</span><h1>Uma coleção. Muitas histórias<span class="lime">.</span></h1><p>Escolha seu clima e deixe a curiosidade fazer o resto.</p></div></section><div class="collections-grid"><?php foreach (
    [
        ['Para jogar em uma tarde', 'orbit', 'Uma pausa na rotina, uma viagem inteira.'],
        ['Feitos de pixels', 'circuit', 'Nostalgia no visual. Originalidade nas ideias.'],
        ['Novos horizontes', 'valley', 'Para quem gosta de descobrir o caminho.'],
    ]
    as [$name, $art, $summary]
): ?><a class="collection-tile" href="<?= url('catalog', ['collection' => $name]) ?>"><img src="<?= asset(
    'images/' . $art . '.svg',
) ?>" alt=""><div><span class="eyebrow">COLEÇÃO EDITORIAL</span><h2><?= $name ?></h2><p><?= $summary ?></p><span class="text-link lime">Explorar coleção <?= icon(
    'arrow',
    18,
) ?></span></div></a><?php endforeach; ?></div>
