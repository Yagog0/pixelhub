<!-- Status e integridade são revalidados no servidor antes da decisão. -->
<form method="post" action="<?= url('admin') ?>" class="admin-review-form">
    <?= csrf_field() ?><input type="hidden" name="action" value="review"><input type="hidden" name="id" value="<?= (int)$project['id'] ?>">
    <label>Justificativa da decisão<textarea name="review_note" minlength="10" maxlength="2000" required rows="2" placeholder="Registre os pontos revisados ou os ajustes necessários."></textarea></label>
    <?php if($project['status']==='pending'): ?>
    <label class="checkbox"><input type="checkbox" name="checked"><span>Revisei manualmente os direitos e a segurança da build.</span></label><div class="actions"><button class="button primary" name="decision" value="published">Aprovar e publicar</button><button class="button secondary" name="decision" value="rejected">Solicitar ajustes</button></div>
    <?php else: ?><button class="button danger" name="decision" value="withdrawn">Retirar do catálogo e bloquear downloads</button><?php endif ?>
</form>
