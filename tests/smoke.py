"""Teste ponta a ponta sem dependências Python e sem tocar no banco do usuário.

Uso: python tests/smoke.py --php D:/php/php.exe
Opcional: PIXELHUB_PLAYWRIGHT aponta para um módulo Playwright já instalado.
"""
import argparse
import http.cookiejar
import io
import json
import os
from pathlib import Path
import re
import socket
import sqlite3
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request
import zipfile

ROOT = Path(__file__).resolve().parents[1]
parser = argparse.ArgumentParser()
parser.add_argument('--php', default='php')
args = parser.parse_args()
checks = 0

def check(condition, message):
    global checks
    assert condition, message
    checks += 1
    print(f'OK {checks:02d}: {message}')

class Browser:
    def __init__(self, base):
        self.base = base
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
    def request(self, page='', data=None, headers=None, raw=False):
        target = self.base + ('index.php?' + page if page else '')
        if isinstance(data, dict): data=urllib.parse.urlencode(data).encode()
        req=urllib.request.Request(target,data=data,headers=headers or {})
        try: response=self.opener.open(req,timeout=10)
        except urllib.error.HTTPError as error: response=error
        content=response.read()
        return response.status, content if raw else content.decode('utf-8'), response.geturl()
    def csrf(self, page='page=login'):
        _,html,_=self.request(page)
        return re.search(r'name="csrf" value="([a-f0-9]+)"',html).group(1)
    def post(self, action, **fields):
        return self.request(data={'csrf':self.csrf('page=profile' if action not in ('login','register') else 'page='+action),'action':action,**fields})

with tempfile.TemporaryDirectory(prefix='pixelhub-test-') as storage:
    with socket.socket() as sock:
        sock.bind(('127.0.0.1',0)); port=sock.getsockname()[1]
    base=f'http://127.0.0.1:{port}/'
    env={**os.environ,'PIXELHUB_STORAGE':storage}
    log=open(Path(storage)/'server.log','w',encoding='utf-8')
    process=subprocess.Popen([args.php,'-d','upload_max_filesize=20M','-d','post_max_size=22M','-S',f'127.0.0.1:{port}','-t',str(ROOT/'public')],cwd=ROOT,env=env,stdout=log,stderr=log)
    try:
        guest=Browser(base)
        for _ in range(40):
            try: guest.request(); break
            except OSError: time.sleep(.1)
        status,html,_=guest.request()
        check(status==200 and 'Seu próximo jogo favorito' in html,'Home funciona e banco inicializa do zero')
        check(html.count('class="game-card"')==4,'Home apresenta quatro jogos editoriais')
        check('Pixels de ontem.' in guest.request('page=discover&feature=1')[1],'Destaques editoriais podem ser alternados')
        for page in ['catalog','collections','random','privacy','about','launcher','play','game&id=1']:
            check(guest.request('page='+page)[0]==200,'Página pública: '+page)
        check('page=login' in guest.request('page=library')[2],'Biblioteca exige autenticação')
        check(guest.request('page=editor&id=1')[0]==200,'Editor redireciona visitante ao login')
        check(guest.request(data={'action':'register'})[0]==419,'POST sem CSRF é recusado')
        status,html,_=guest.request('page=catalog&q=Jardim')
        check(html.count('class="game-card"')==1 and 'Jardim Lunar' in html,'Pesquisa por título funciona')
        status,html,_=guest.request('page=catalog&genre=Puzzle&platform=Windows')
        check(html.count('class="game-card"')==1,'Filtros combinados funcionam')
        check('Nenhum jogo encontrado' in guest.request('page=catalog&q=naoexiste123')[1],'Pesquisa vazia tem estado apropriado')
        check('id=1' in guest.request('page=random&draw=1&platform=Navegador')[2],'Sorteio respeita plataforma')
        check(guest.request('page=game&id=99999')[0]==404,'Projeto inexistente retorna 404')
        check('&lt;script&gt;' in guest.request('page=catalog&q=%3Cscript%3E')[1],'Busca escapa HTML')
        creator=Browser(base)
        status,html,_=creator.post('register',name='Criador Teste',email='creator@example.test',password='StrongPassword123!',terms='1')
        check(status==200 and 'Sua conta está pronta' in html,'Cadastro cria conta e autentica')
        check(creator.request('page=admin')[0]==403,'Conta comum não acessa administração')
        check(creator.request('page=editor&id=1')[0]==404,'Conta comum não edita projeto alheio')
        for project in [1,2,3]: creator.post('library',id=project)
        check(creator.request('page=library')[1].count('class="library-card panel"')==3,'Biblioteca persiste três jogos')
        creator.post('library',id=1)
        check(creator.request('page=library')[1].count('class="library-card panel"')==3,'Adicionar duas vezes é idempotente')
        creator.post('favorite',id=1)
        check(creator.request('page=favorites')[1].count('class="library-card panel"')==1,'Favoritos são filtrados')
        check(creator.request('page=achievements')[1].count('achievement-card unlocked')==3,'Conquistas de biblioteca desbloqueiam')
        status,body,_=creator.post('game_achievement',key='first-flight')
        check(json.loads(body)['unlocked']=='Decolagem autorizada','Conquista do minijogo persiste')
        check(json.loads(creator.post('game_achievement',key='first-flight')[1])['unlocked'] is None,'Conquista não duplica')
        creator.post('profile',name='Criador Atualizado',bio='<script>teste</script>')
        check('&lt;script&gt;teste&lt;/script&gt;' in creator.request('page=profile')[1],'Perfil salva dados com escape HTML')
        exported=json.loads(creator.request('page=export')[1])
        check('password' not in exported['profile'] and len(exported['library'])==3,'Exportação respeita conta e não inclui hash de senha')
        check('achievements' in exported,'Exportação inclui as conquistas do jogador')
        fields={'title':'Jogo de Teste','summary':'Uma aventura original de teste.','description':'Um projeto original para validar todo o fluxo de publicação.','genre':'Aventura','type':'Jogo completo','platform':'Windows','language':'Português','duration':'30 minutos','requirements':'Windows 10','controls':'Teclado','content_notice':'Sem avisos','license':'Autoria própria','rights':'Código e assets autorais do criador de teste.','art':'valley','version':'1.0.0','changelog':'Primeira versão.'}
        status,html,_=creator.post('save_project',**fields)
        check('Rascunho salvo' in html,'Criador salva rascunho')
        db=sqlite3.connect(Path(storage)/'pixelhub.sqlite')
        project_id=db.execute('SELECT id FROM projects WHERE owner_id IS NOT NULL').fetchone()[0]
        check(guest.request(f'page=game&id={project_id}')[0]==404,'Rascunho não vaza para visitante')
        check('Antes de enviar' in creator.post('submit',id=project_id)[1],'Envio sem build é bloqueado')
        creator.post('achievement_definition',id=project_id,key='first-win',title='Primeira vitória',description='Conclua a primeira fase do jogo.')
        check('Primeira vitória' in creator.request(f'page=editor&id={project_id}')[1],'Criador define conquista')
        # Upload multipart real, com ZIP gerado somente para o teste.
        zipped=io.BytesIO()
        with zipfile.ZipFile(zipped,'w') as archive: archive.writestr('README.txt','Build de teste original. Nao executar.')
        boundary='PixelHubTestBoundary93847'
        upload_fields={**fields,'id':project_id,'action':'save_project','csrf':creator.csrf('page=profile')}
        payload=b''
        for key,value in upload_fields.items(): payload+=f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n'.encode()
        payload+=f'--{boundary}\r\nContent-Disposition: form-data; name="build"; filename="build.zip"\r\nContent-Type: application/zip\r\n\r\n'.encode()+zipped.getvalue()+f'\r\n--{boundary}--\r\n'.encode()
        status,html,_=creator.request(data=payload,headers={'Content-Type':'multipart/form-data; boundary='+boundary})
        check('Rascunho salvo' in html,'Upload ZIP real é aceito')
        check('Projeto enviado para revisão' in creator.post('submit',id=project_id)[1],'Submissão entra em revisão')
        check('não podem ser alterados' in creator.post('save_project',id=project_id,**fields)[1],'Projeto em revisão é imutável')
        attacker=Browser(base); attacker.post('register',name='Outro Jogador',email='other@example.test',password='StrongPassword123!',terms='1')
        check(attacker.request(f'page=editor&id={project_id}')[0]==404,'Segundo usuário não edita projeto de outro criador')
        check(attacker.request(f'page=download&id={project_id}&review=1')[0]==404,'Parâmetro review não contorna autorização')
        admin=Browser(base);admin.post('register',name='Admin Teste',email='admin@example.test',password='StrongPassword123!',terms='1')
        db.execute("UPDATE users SET role='admin' WHERE email='admin@example.test'");db.commit()
        check(admin.request('page=admin')[0]==200,'Administrador acessa fila')
        check(admin.request(f'page=download&id={project_id}&review=1',raw=True)[1]==zipped.getvalue(),'Administrador pode revisar ZIP privado')
        check('Confirme a revisão' in admin.post('review',id=project_id,decision='published',review_note='Revisado no teste automatizado.')[1],'Aprovação exige confirmação manual')
        check('Revisão registrada' in admin.post('review',id=project_id,decision='published',review_note='Revisado no teste automatizado.',checked='1')[1],'Revisor publica projeto')
        check(guest.request(f'page=game&id={project_id}')[0]==200,'Projeto aprovado aparece publicamente')
        check(attacker.request(f'page=download&id={project_id}')[0]==404,'Download exige biblioteca')
        attacker.post('library',id=project_id)
        check(attacker.request(f'page=download&id={project_id}',raw=True)[1]==zipped.getvalue(),'Jogador baixa exatamente o ZIP aprovado')
        _,html,_=attacker.post('game_token',id=project_id)
        token=re.search(r'<code class="hash-code">([a-f0-9]{64})</code>',html).group(1)
        payload=json.dumps({'key':'first-win'}).encode()
        headers={'Authorization':'Bearer '+token,'Content-Type':'application/json'}
        result=guest.request('page=api-unlock',data=payload,headers=headers)
        check(json.loads(result[1])['new_unlock'] is True,'API registra conquista com token do jogador/projeto')
        check(json.loads(guest.request('page=api-unlock',data=payload,headers=headers)[1])['new_unlock'] is False,'API deduplica conquistas')
        check(guest.request('page=api-unlock',data=json.dumps({'key':'first-flight'}).encode(),headers=headers)[0]==422,'Token não desbloqueia conquista de outro projeto')
        check(guest.request('page=api-unlock',data=payload,headers={'Authorization':'Bearer invalid'})[0]==401,'API recusa token inválido')
        check('Relato registrado' in attacker.post('report',id=project_id,reason='Relato de teste para validar a moderação.')[1],'Denúncia fica registrada')
        report_id=db.execute('SELECT id FROM reports').fetchone()[0]
        admin.post('resolve',id=report_id)
        check(db.execute('SELECT status FROM reports WHERE id=?',(report_id,)).fetchone()[0]=='resolved','Moderação encerra relato')
        admin.post('review',id=project_id,decision='withdrawn',review_note='Retirada para teste de segurança.')
        check(attacker.request(f'page=download&id={project_id}')[0]==404,'Retirada bloqueia novo download')
        check(guest.request('page=api-unlock',data=payload,headers=headers)[0]==401,'Retirada revoga acesso à API do projeto')
        check(guest.request(f'page=game&id={project_id}')[0]==404,'Retirada remove página pública')
        from admin_checks import run as admin_checks
        admin_checks(check,Browser,base,admin,attacker,guest,db,args.php,env,ROOT)
        creator.post('logout')
        check('page=login' in creator.request('page=profile')[2],'Logout encerra acesso')
        check('E-mail ou senha incorretos' in creator.post('login',email='creator@example.test',password='wrong')[1],'Login recusa senha incorreta')
        check('Bom ter você de volta' in creator.post('login',email='creator@example.test',password='StrongPassword123!')[1],'Login aceita senha correta')
        if os.environ.get('PIXELHUB_PLAYWRIGHT'):
            subprocess.run(['node',str(ROOT/'tests/browser.cjs'),base],cwd=ROOT,env=env,check=True)
        db.close()
        print(f'\n{checks} verificacoes HTTP aprovadas. Banco de teste isolado.')
    finally:
        process.terminate();process.wait(timeout=10);log.close()
