<?php require __DIR__.'/filters.php'; ?>
<section class="panel"><h2>Quem chegou à PixelHub</h2><p class="small muted">Contas reais deste ambiente. Senhas e chaves pessoais não são exibidas. Permissões administrativas são concedidas pelo responsável no terminal.</p><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Conta</th><th>E-mail</th><th>Acesso</th><th>Projetos</th><th>Cadastro (UTC)</th></tr></thead><tbody>
<?php foreach($rows as $account): ?><tr><td><?= e($account['name']) ?></td><td><?= e($account['email']) ?></td><td><span class="badge"><?= $account['role']==='admin'?'Administrador':'Membro' ?></span></td><td><?= (int)$account['projects'] ?></td><td><?= e($account['created_at']) ?></td></tr><?php endforeach ?>
<?php if(!$rows): ?><tr><td colspan="5">Nenhuma conta encontrada.</td></tr><?php endif ?>
</tbody></table></div></section>
