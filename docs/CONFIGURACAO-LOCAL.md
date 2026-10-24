# PixelHub — PHP

Plataforma web inspirada no `PixelHub_Plano_Completo.pdf`, adaptada para PHP conforme solicitado. Visual original, português brasileiro, layout responsivo, comentários por responsabilidade e MVC simples sem framework.

## Abrir agora

Servidor local: **http://127.0.0.1:8081/**

Para iniciar novamente no PowerShell, dentro da pasta do projeto:

```powershell
.\scripts\start.ps1
```

Ou:

```powershell
D:\php\php.exe -d upload_max_filesize=20M -d post_max_size=22M -S 127.0.0.1:8081 -t public
```

Requisitos: PHP 8.2+ com PDO SQLite, mbstring e fileinfo. Não precisa de Composer, Node, MySQL ou conexão externa para executar o site. SQLite cria as tabelas e os dados iniciais automaticamente. A busca simples funciona por título/criador; filtros incluem gênero, tipo, plataforma e coleção.

## Apache e index

`index.php` na raiz é a entrada do projeto e encaminha para `public/index.php`. `scripts/apache-pixelhub.conf` define `DirectoryIndex index.php`, o handler PHP e bloqueia o acesso às pastas internas. A configuração foi incluída no Apache local, preservando backups `.pixelhub-backup`; é necessário recarregar o processo do Apache para aplicar. Como o processo atual não é um serviço registrado, `httpd -k restart` não o reiniciou.

Enquanto isso, `index.html` é uma ponte LOCAL para a porta 8081: o Apache que ainda procura apenas `index.html` abre o site em vez de listar as pastas. Remova essa ponte ao publicar em outro ambiente. Em produção, configure o DocumentRoot diretamente em `public/`, HTTPS e os bloqueios adequados do servidor. Nunca sirva arquivos PHP como texto.

## Funcionalidades implementadas

- Cadastro, login, logout, senha com hash, CSRF, sessão regenerada e limite de tentativas.
- Catálogo, busca, filtros combinados, ordenação, coleções e páginas de jogo.
- Sorteio aleatório por gênero/plataforma/tipo, inclusive dentro da biblioteca.
- Biblioteca privada, favoritos, perfil editável e exportação dos dados.
- Conquistas de exploração e do minijogo; definição de conquistas pelo criador e API por token de escopo limitado.
- Painel do criador: até 3 projetos, rascunho, prévia, metadados, direitos, build ZIP privada de até 20 MB, SHA-256 e envio para revisão.
- Moderação: aprovação manual, solicitação de ajustes, retirada com bloqueio imediato de novos downloads, denúncias e auditoria.
- Download da build aprovada para usuários com o jogo na biblioteca, com verificação de integridade.
- Minijogo original Órbita de Papel com teclado/toque, pausa, recorde local e conquistas.
- Artes SVG locais e originais, sem fontes ou imagens de terceiros.

## Criar um administrador

O painel completo está em **http://127.0.0.1:8081/caba%C3%A7o.php**. Permite acompanhar envios e indicadores, organizar a vitrine e editar a página inicial. Veja [o guia do painel](PAINEL-ADMIN.md). Para uma conta ainda não cadastrada, `D:\php\php.exe scripts/invite-admin.php email@exemplo.com` gera uma ativação de uso único para definir a senha.

Cadastre sua conta normalmente. Depois execute no terminal:

```powershell
D:\php\php.exe scripts/admin.php seu-email@exemplo.com
```

Atualize a página e acesse **Moderação**. Não existe conta administrativa ou senha padrão. O terminal e o servidor precisam usar o mesmo diretório de dados: se rodarem com usuários diferentes, defina `PIXELHUB_STORAGE` explicitamente para ambos.

## Pastas

```text
app/
  Controllers/   # Rotas de leitura, ações e validações do servidor
  Core/          # Conexão PDO e inicialização
  Models/        # Consultas de projetos e conquistas
  bootstrap.php  # Autoload, sessão e cabeçalhos
  helpers.php    # Escape HTML, URLs, CSRF e componentes
config/          # Caminho privado e limites da aplicação
database/        # Schema SQL e seeds demonstrativos
docs/            # Integração e decisões de implementação
public/
  index.php      # Front controller recomendado
  assets/css/    # Tokens, layout, componentes e responsividade
  assets/js/     # Interação progressiva e minijogo
  assets/images/ # Ilustrações SVG originais
scripts/         # Inicialização, administração e geração das artes
tests/           # Verificações HTTP e interface
views/           # Telas PHP e parciais reutilizáveis
index.php        # Entrada para Apache em subpasta
```

## Dados e backup

O banco e as builds ficam fora do `htdocs`, em `%LOCALAPPDATA%/PixelHub/<identificador-da-pasta>/`. Se LOCALAPPDATA não existir, é usado o temporário do sistema. Para escolher armazenamento persistente, configure `PIXELHUB_STORAGE` antes de iniciar o PHP. Veja o caminho efetivo com:

```powershell
D:\php\php.exe scripts/status.php
```

Pare o servidor antes de copiar a pasta inteira para backup (banco, WAL se existir, e builds). Para restaurar, use uma cópia íntegra dessa pasta e a mesma versão do código. Não execute testes contra o banco de uso pessoal; o teste automatizado cria seu próprio ambiente temporário.

## Limites desta versão

O documento é um plano de startup, e não uma especificação apenas de site. Esta entrega é uma implementação web funcional local, não a totalidade do produto de produção descrito no PDF. Launcher Windows, atualização/rollback, assinatura de manifests, aplicação IPS/UPS/BPS, pagamentos, e-mail de recuperação/verificação, MFA, organizações/equipes, antivírus automático, CDN, paginação e exclusão de conta pela interface continuam pendentes. Builds publicadas são imutáveis neste protótipo; múltiplas releases por projeto ainda não foram implementadas. Os uploads não são extraídos nem executados pelo servidor.

Os seis projetos iniciais e seus estúdios são fictícios. Somente Órbita de Papel inclui uma experiência jogável. Sem métricas de jogadores ou avaliações inventadas. Conquistas informadas pelo jogo não equivalem a validação antitrapaça. As artes e o minijogo foram criados como parte deste projeto.

Configuração pública exige concluir as dependências de operação e segurança do plano, inclusive política de dados, recuperação de contas, revisão de conteúdo e distribuição. Não foi feita validação de marca ou domínio.

## Verificação e formatação

```powershell
python tests/smoke.py --php D:/php/php.exe
```

O teste sobe um servidor temporário com banco isolado e verifica os fluxos HTTP reais. `tests/browser.cjs` complementa com testes em desktop/celular e screenshots quando Playwright está instalado. O módulo pode ser informado pela variável `PIXELHUB_PLAYWRIGHT`. A atualização do painel passou em 92 verificações HTTP e nos fluxos de navegador, incluindo todas as abas administrativas em celular.

Node é opcional e só é usado para desenvolvimento: `npm install` e `npm run format` formatam os arquivos PHP, JavaScript e CSS. O site não depende de `node_modules`.

