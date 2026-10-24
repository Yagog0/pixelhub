<section class="panel admin-setup">
    <span class="eyebrow">SEU ACESSO À CENTRAL DE CONTROLE</span>
    <h1>Vamos preparar seu painel<span class="lime">.</span></h1>
    <p class="muted">Defina sua senha para ativar a conta administrativa <strong><?= e($invitation['email']) ?></strong>. Este convite só pode ser utilizado uma vez.</p>
    <form method="post" action="<?= url('admin-setup') ?>">
        <?= csrf_field() ?><input type="hidden" name="action" value="activate_admin">
        <label>Seu nome<input name="name" required minlength="2" maxlength="60" autocomplete="name"></label>
        <label>Senha<input type="password" name="password" required minlength="10" maxlength="200" autocomplete="new-password" placeholder="Pelo menos 10 caracteres"></label>
        <label>Confirme a senha<input type="password" name="confirm_password" required minlength="10" maxlength="200" autocomplete="new-password"></label>
        <button class="button primary full">Ativar e entrar no painel <?= icon('arrow',18) ?></button>
    </form>
</section>
