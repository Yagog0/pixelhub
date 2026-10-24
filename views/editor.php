<?php $values = $_SESSION['old_project'] ?? ($game ?? []);
unset($_SESSION['old_project']);
$locked = $game && !in_array($game['status'], ['draft', 'rejected']);
?>
<a class="back-link" href="<?= url(
    'creator',
) ?>">← Voltar aos seus projetos</a><section class="page-heading"><div><span class="eyebrow">ESPAÇO DO CRIADOR</span><h1><?= e(
    $title,
) ?><span class="lime">.</span></h1><p>Conte a história do seu jogo. Você pode salvar e continuar depois.</p></div></section>
<?php if ($locked): ?><div class="notice">Este projeto está <?= e(
    status_label($game['status']),
) ?>. A edição foi bloqueada para preservar a versão enviada.</div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="editor-form"><?= csrf_field() ?><input type="hidden" name="action" value="save_project"><input type="hidden" name="id" value="<?= e(
    $game['id'] ?? '',
) ?>"><fieldset <?= $locked
    ? 'disabled'
    : '' ?>><section class="panel"><span class="eyebrow">01 / IDENTIDADE</span><h2>Apresente seu universo</h2><div class="form-grid"><label>Nome do jogo<input name="title" required minlength="3" maxlength="80" value="<?= e(
    $values['title'] ?? '',
) ?>" placeholder="Como se chama sua próxima aventura?"></label><label>Versão<input name="version" required pattern="[0-9]+\.[0-9]+\.[0-9]+" value="<?= e(
    $values['version'] ?? '1.0.0',
) ?>" placeholder="1.0.0"></label></div><label>Resumo<input name="summary" required minlength="10" maxlength="180" value="<?= e(
    $values['summary'] ?? '',
) ?>" placeholder="Uma frase para despertar a curiosidade"></label><label>Descrição<textarea name="description" rows="5" required minlength="20" maxlength="6000"><?= e(
    $values['description'] ?? '',
) ?></textarea></label><div class="form-grid three"><?php foreach (
    [
        'genre' => ['Gênero', ['Aventura', 'Puzzle', 'Plataforma', 'RPG', 'Corrida', 'Exploração']],
        'type' => ['Tipo de projeto', ['Jogo completo', 'Demo', 'Homebrew']],
        'platform' => ['Plataforma', ['Windows', 'Linux', 'Navegador']],
    ]
    as $key => $options
): ?><label><?= $options[0] ?><select name="<?= $key ?>"><?php foreach (
    $options[1]
    as $value
): ?><option <?= ($values[$key] ?? '') === $value ? 'selected' : '' ?>><?= e(
    $value,
) ?></option><?php endforeach; ?></select></label><?php endforeach; ?></div><label>Capa ilustrativa<select name="art"><?php foreach (
    [
        'orbit' => 'Órbita violeta',
        'garden' => 'Jardim lunar',
        'circuit' => 'Circuito neon',
        'valley' => 'Vale das nuvens',
        'fire' => 'Fogueira',
        'sea' => 'Mar azul',
    ]
    as $key => $label
): ?><option value="<?= $key ?>" <?= ($values['art'] ?? 'orbit') === $key
    ? 'selected'
    : '' ?>><?= $label ?></option><?php endforeach; ?></select></label><p class="small muted">Artes originais do protótipo disponíveis para a prévia do seu projeto.</p></section>
<section class="panel"><span class="eyebrow">02 / EXPERIÊNCIA</span><h2>Prepare quem vai jogar</h2><div class="form-grid"><label>Idiomas<input name="language" maxlength="80" value="<?= e(
    $values['language'] ?? 'Português',
) ?>"></label><label>Duração aproximada<input name="duration" maxlength="80" value="<?= e(
    $values['duration'] ?? '',
) ?>" placeholder="Ex.: 30 minutos"></label></div><label>Requisitos mínimos<textarea name="requirements" maxlength="1500" rows="3" placeholder="Sistema operacional, memória e espaço necessário"><?= e(
    $values['requirements'] ?? '',
) ?></textarea></label><label>Controles<input name="controls" maxlength="500" value="<?= e(
    $values['controls'] ?? 'Teclado e mouse',
) ?>"></label><label>Avisos de conteúdo<input name="content_notice" maxlength="500" value="<?= e(
    $values['content_notice'] ?? 'Sem avisos informados',
) ?>"></label></section>
<section class="panel"><span class="eyebrow">03 / DIREITOS E BUILD</span><h2>Uma publicação responsável</h2><label>Licença de distribuição<input name="license" maxlength="500" value="<?= e(
    $values['license'] ?? '',
) ?>" placeholder="Informe a licença e as condições de distribuição"></label><label>Evidências e autorização de direitos<textarea name="rights" maxlength="4000" rows="4" placeholder="Descreva a titularidade, a origem dos assets e referências da autorização de distribuição. Este campo é privado."><?= e(
    $values['rights'] ?? '',
) ?></textarea></label><label>Notas desta versão<textarea name="changelog" maxlength="2000" rows="3"><?= e(
    $values['changelog'] ?? 'Primeira versão.',
) ?></textarea></label><label class="upload-field"><?= icon(
    'download',
    26,
) ?><strong>Build do jogo em ZIP</strong><span>Até 20 MB · armazenada em área privada para revisão</span><input type="file" name="build" accept=".zip,application/zip"></label><?php if (
    $game &&
    $game['build_path']
): ?><p class="small lime">Build anexada · <?= number_format(
    $game['build_size'] / 1048576,
    2,
    ',',
    '.',
) ?> MB. Enviar outro arquivo substitui apenas este rascunho.</p><?php endif; ?><p class="small muted">O sistema calcula o SHA-256, mas não faz análise antivírus. Um arquivo anexado não é automaticamente aprovado.</p></section><div class="actions"><button class="button primary">Salvar rascunho <?= icon(
    'check',
    18,
) ?></button><a class="button secondary" href="<?= url('creator') ?>">Voltar</a></div></fieldset></form>
<?php if ($game):
    $definitions = App\Core\Database::all('SELECT * FROM achievements WHERE project_id=? ORDER BY id', [
        $game['id'],
    ]); ?><section class="panel creator-achievements"><span class="eyebrow">04 / CONQUISTAS</span><h2>Dê novos motivos para explorar</h2><p class="muted small">As chaves abaixo podem ser integradas ao seu jogo pela API. Não são desbloqueadas automaticamente por enviar uma build.</p><?php
foreach ($definitions as $definition): ?><div class="definition-row"><span><?= icon(
    'trophy',
    18,
) ?> <strong><?= e($definition['title']) ?></strong><small><?= e(
    $definition['description'],
) ?></small></span><code><?= e($definition['key']) ?></code></div><?php endforeach;
if (
    !$locked
): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="achievement_definition"><input type="hidden" name="id" value="<?= $game[
    'id'
] ?>"><div class="form-grid"><label>Chave estável<input name="key" pattern="[a-z0-9-]{3,60}" required maxlength="60" placeholder="primeira-vitoria"></label><label>Título<input name="title" minlength="3" required maxlength="100" placeholder="A primeira de muitas"></label></div><label>Como desbloquear<input name="description" minlength="10" required maxlength="500" placeholder="Complete a primeira fase do jogo."></label><label class="checkbox"><input type="checkbox" name="secret"><span>Manter título e descrição em segredo até o desbloqueio.</span></label><button class="button secondary">Salvar conquista <?= icon(
    'trophy',
    17,
) ?></button></form><?php endif;
?><p class="small muted">Integração: POST <code>index.php?page=api-unlock</code> com chave do jogador no cabeçalho Bearer. Consulte <code>docs/INTEGRACAO.md</code> no projeto.</p></section><?php
else:
     ?><div class="notice">Salve o primeiro rascunho para definir as conquistas do seu jogo.</div><?php
endif; ?>
