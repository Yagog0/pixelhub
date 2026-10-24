"""Contratos administrativos executados dentro do banco isolado do smoke test."""
import json
import re
import subprocess

def run(check, Browser, base, admin, member, guest, db, php, env, root):
    # A entrada com acento deve chegar ao mesmo controle de autorização.
    response=admin.opener.open(base+'caba%C3%A7o.php')
    check(response.status==200 and 'Você no controle' in response.read().decode(), 'Arquivo cabaço.php abre o painel')
    for tab in ['overview','queue','catalog','appearance','users','reports','history']:
        code,html,_=admin.request('page=admin&tab='+tab)
        check(code==200 and 'Seções administrativas' in html,'Aba administrativa: '+tab)
    check(member.post('admin_display',id=1,position=0,collection='Novos horizontes',featured='1')[0]==403,'Membro não altera curadoria por POST')
    check(guest.request('page=admin',data={'action':'admin_reset'})[0]==419,'Configuração administrativa exige CSRF')
    check(admin.request('page=admin&tab=../../config/app')[0]==200,'Nome de aba usa lista fechada')
    admin.post('admin_display',id=1,position=0,collection='Novos horizontes',featured='1')
    html=guest.request()[1]
    check(re.search(r'class="card-title".*?id=1',html,re.S) is not None and html.index('Órbita de Papel')<html.index('Mar de Dentro'),'Destaque manual vai ao início da home')
    check('Órbita de Papel' in guest.request('page=catalog&collection=Novos+horizontes')[1],'Coleção manual entra nos filtros públicos')
    admin.post('admin_display',id=1,position=0,collection='Novos horizontes',hidden='1')
    check('Nenhum jogo encontrado' in guest.request('page=catalog&q=Est%C3%BAdio+Dobradura')[1],'Ocultar retira o jogo da busca')
    check('page=random' in guest.request('page=random&draw=1&platform=Navegador')[2],'Sorteio não seleciona jogo oculto')
    check(guest.request('page=game&id=1')[0]==200,'Ocultar vitrine preserva acesso direto ao jogo')
    check(db.execute('SELECT status FROM projects WHERE id=1').fetchone()[0]=='published','Curadoria não altera o estado da publicação')
    admin.post('admin_auto',id=1)
    check('Órbita de Papel' in guest.request('page=catalog&q=Est%C3%BAdio+Dobradura')[1],'Voltar ao automático restaura a descoberta')
    settings={'heading':'Seleção <script>nova</script>','heading_line2':'Escolhida pela equipe','subtitle':'Uma nova descrição editorial.','section_title':'Jogos escolhidos por nós','home_count':'8','automatic_order':'name','show_demos':'1','default_feature':'2'}
    for i in range(3):
        settings.update({f'feature_{i}_art':'garden',f'feature_{i}_collection':'Novos horizontes',f'feature_{i}_kicker':'DESTAQUE EDITORIAL',f'feature_{i}_title_0':f'Novo banner {i}',f'feature_{i}_title_1':'Uma nova aventura',f'feature_{i}_description_0':'Descubra nossa seleção.',f'feature_{i}_description_1':'Feita para você.'})
    check('Página inicial atualizada' in admin.post('admin_appearance',**settings)[1],'Aparência e banners são salvos')
    html=guest.request()[1]
    check('Seleção &lt;script&gt;nova&lt;/script&gt;' in html and '<script>nova</script>' not in html,'Texto editorial chega à home com escape HTML')
    check('Novo banner 2' in html and html.count('class="game-card"')==6,'Banner inicial e quantidade de cards são configuráveis')
    check('Escolha uma opção válida' in admin.post('admin_appearance',**{**settings,'feature_0_art':'../../config/app'})[1],'Capa fora da lista é recusada')
    check('Novo banner 2' in guest.request()[1],'Erro de validação preserva as configurações anteriores')
    no_demos=settings.copy();del no_demos['show_demos'];admin.post('admin_appearance',**no_demos)
    check(guest.request('page=catalog')[1].count('class="game-card"')==0,'Projetos fictícios podem ser ocultados globalmente')
    admin.post('admin_reset')
    check('Seu próximo jogo favorito' in guest.request()[1],'Restaurar recupera a aparência padrão')
    check('Aparência da página inicial atualizada' in admin.request('page=admin&tab=history')[1],'Histórico identifica mudanças editoriais')
    check(db.execute('SELECT COUNT(*) FROM download_events').fetchone()[0]==1,'Métrica conta download autorizado sem incluir revisão administrativa')
    # Ativação é por token secreto emitido no terminal, nunca somente por e-mail.
    output=subprocess.check_output([php,str(root/'scripts/invite-admin.php'),'invite@example.test'],env=env,cwd=root,text=True,encoding='utf-8')
    token=re.search(r'token=([a-f0-9]{64})',output).group(1)
    invited=Browser(base)
    check(invited.request('page=admin-setup&token=invalid')[0]==403,'Convite inválido não abre ativação')
    check('invite@example.test' in invited.request('page=admin-setup&token='+token)[1],'Convite válido mostra o e-mail autorizado')
    csrf=invited.csrf('page=admin-setup')
    code,html,_=invited.request('page=admin-setup',data={'csrf':csrf,'action':'activate_admin','name':'Administrador Convidado','password':'VeryStrongPass123!','confirm_password':'VeryStrongPass123!'})
    check(code==200 and 'Você no controle' in html,'Convite cria administrador e abre o painel')
    check(Browser(base).request('page=admin-setup&token='+token)[0]==403,'Convite administrativo não pode ser reutilizado')
    check(db.execute("SELECT role FROM users WHERE email='invite@example.test'").fetchone()[0]=='admin','Papel administrativo é persistido')
