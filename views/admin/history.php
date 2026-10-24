<section class="panel"><span class="eyebrow">DECISÕES RASTREÁVEIS</span><h2>O que mudou e quem mudou</h2><p class="small muted">Publicações, revisões e alterações de exibição ficam registradas. Os registros não podem ser editados pelo painel.</p><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Quando (UTC)</th><th>Responsável</th><th>Projeto</th><th>Ação</th></tr></thead><tbody>
<?php foreach($rows as $event): ?><tr><td><?= e($event['created_at']) ?></td><td><?= e($event['name']??'Sistema') ?></td><td><?= e($event['title']??'Plataforma') ?></td><td><?= e($event['action']) ?></td></tr><?php endforeach ?>
<?php if(!$rows): ?><tr><td colspan="4">As próximas decisões aparecerão aqui.</td></tr><?php endif ?>
</tbody></table></div></section>
