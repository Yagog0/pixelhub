<!-- Indicadores reais: downloads não são apresentados como instalações. -->
<div class="admin-metrics">
<?php foreach([
    ['pending','Aguardando revisão','Envios que precisam da sua atenção','queue','code'],
    ['published','Jogos publicados','Inclui o catálogo demonstrativo','catalog','grid'],
    ['accounts','Contas cadastradas','Cadastros deste ambiente','users','user'],
    ['reports','Denúncias abertas','Relatos que precisam de análise','reports','shield'],
] as [$key,$label,$hint,$target,$symbol]): ?>
    <a class="panel admin-metric" href="<?= url('admin',['tab'=>$target]) ?>"><span><?= icon($symbol,19) ?> <?= $label ?></span><strong><?= (int)$stats[$key] ?></strong><small><?= $hint ?></small></a>
<?php endforeach ?>
</div>
<div class="admin-two-columns">
    <section class="panel">
        <div class="section-heading"><div><span class="eyebrow">A PRÓXIMA DESCOBERTA COMEÇA AQUI</span><h2>Acabou de chegar</h2></div><a class="text-link lime" href="<?= url('admin',['tab'=>'queue','status'=>'pending']) ?>">Ver fila →</a></div>
        <?php if(!$latest): ?><div class="admin-empty"><?= icon('check',30) ?><h3>Tudo em dia por aqui.</h3><p>Os projetos enviados para revisão aparecem automaticamente nesta fila.</p></div><?php endif ?>
        <?php foreach($latest as $project): ?><a class="admin-compact-row" href="<?= url('admin',['tab'=>'queue','q'=>$project['title']]) ?>"><img src="<?= asset('images/'.$project['art'].'.svg') ?>" alt=""><span><strong><?= e($project['title']) ?></strong><small><?= e($project['creator']) ?> · v<?= e($project['version']) ?></small></span><span class="badge pending">Em revisão</span></a><?php endforeach ?>
    </section>
    <section class="panel admin-rule-card"><span class="eyebrow">A PLATAFORMA CONTINUA ANDANDO</span><h2>Automático por padrão.<br>Seu toque quando precisar.</h2><ol><li>Criadores enviam os jogos para a fila.</li><li>Você revisa os arquivos e os direitos.</li><li>Jogos aprovados entram no catálogo e na seleção da home.</li><li>Se quiser, fixe destaques e ajuste a ordem.</li></ol><a class="button primary" href="<?= url('admin',['tab'=>'catalog']) ?>">Organizar a vitrine <?= icon('arrow',17) ?></a></section>
</div>
<div class="admin-metrics secondary-metrics">
<?php foreach([['additions','Adições atuais à biblioteca'],['downloads','Downloads iniciados'],['unlocks','Conquistas desbloqueadas'],['real_projects','Projetos de criadores']] as [$key,$label]): ?><div class="panel admin-metric"><span><?= $label ?></span><strong><?= (int)$stats[$key] ?></strong></div><?php endforeach ?>
</div>
<div class="admin-two-columns">
    <section class="panel"><span class="eyebrow">ÚLTIMOS SETE DIAS</span><h2>Como está indo</h2><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Dia (UTC)</th><th>Cadastros</th><th>Biblioteca</th><th>Downloads</th></tr></thead><tbody>
    <?php foreach($activity as $day): ?><tr><td><?= date('d/m',strtotime($day['day'])) ?></td><td><?= (int)$day['accounts'] ?></td><td><?= (int)$day['additions'] ?></td><td><?= (int)$day['downloads'] ?></td></tr><?php endforeach ?>
    </tbody></table></div><p class="small muted admin-footnote">Biblioteca conta entradas ainda existentes. Downloads registram inícios autorizados desde a criação deste painel; não comprovam conclusão ou instalação.</p></section>
    <section class="panel"><span class="eyebrow">O QUE ESTÃO GUARDANDO</span><h2>Mais presentes nas bibliotecas</h2>
    <?php if(!$ranking): ?><p class="muted">Quando jogadores guardarem seus primeiros jogos, os resultados aparecerão aqui.</p><?php endif ?>
    <?php foreach($ranking as $game): ?><a class="admin-compact-row" href="<?= url('game',['id'=>$game['id']]) ?>"><img src="<?= asset('images/'.$game['art'].'.svg') ?>" alt=""><span><strong><?= e($game['title']) ?></strong><small><?= (int)$game['total'] ?> adições atuais</small></span><?= icon('library',18) ?></a><?php endforeach ?>
    </section>
</div>
