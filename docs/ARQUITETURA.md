# Mapa de manutenção

## Uma requisição

1. `index.php` encaminha a execução para `public/index.php`.
2. `app/bootstrap.php` carrega classes, configura a sessão e abre o SQLite.
3. `SiteController` responde às páginas GET. `ActionController` valida e executa POSTs.
4. `Models` agrupam consultas reutilizáveis. Toda entrada variável usa parâmetros PDO.
5. `render()` coloca a tela dentro de `views/layout.php`. `e()` protege conteúdo dinâmico no HTML.

Rotas usam `index.php?page=...` para funcionar sem `mod_rewrite`. O helper `asset()` atende tanto o DocumentRoot em `public/` quanto a instalação em subpasta no Apache.

## Publicação

`draft → pending → published`, ou `pending → rejected → draft`. Um administrador pode passar `published → withdrawn`. Apenas o dono altera rascunhos; somente administradores decidem revisões. Versões em revisão/publicadas não são sobrescritas.

O ZIP recebe um nome aleatório e fica fora da raiz HTTP. O servidor verifica tamanho, MIME e SHA-256, mas não o extrai nem executa. Downloads públicos exigem autenticação, biblioteca, status publicado e hash intacto. A revisão administrativa permite obter o arquivo privado para análise externa.

A tabela `projects` contém uma única build por projeto neste protótipo. O próximo incremento para releases deve separar os artefatos em tabela própria e manter as referências imutáveis. Não deve simplesmente liberar edição do arquivo publicado.

## Conquistas

`achievements` guarda definições por projeto ou plataforma. `unlocks` tem chave composta por jogador e conquista, evitando duplicações. Marcos de biblioteca são computados no servidor. Eventos do minijogo e de clientes externos são identificados como declarações do cliente, sem valor competitivo.

`game_tokens` armazena somente o hash da chave, com prazo e escopo de usuário/projeto. O token original é exibido uma vez. Chaves administrativas nunca são distribuídas no jogo. Veja `INTEGRACAO.md`.

## Onde alterar o visual

- Paleta, medidas e fontes: tokens no início de `public/assets/css/app.css`.
- Menu e cabeçalho: `views/layout.php`.
- Home e destaques: `views/discover.php`.
- Card reutilizado no catálogo: `views/partials/card.php`.
- Ilustrações: SVGs em `public/assets/images/`; gerador comentado em `scripts/generate-art.py`.
- Comportamentos progressivos: `public/assets/js/app.js`.
- Minijogo: `public/assets/js/game.js`.

## Escopo da implementação

As recomendações de entrevistas, contratação, registro de marca, marketing e outras operações do PDF são contexto do produto, não comandos executados pelo sistema. Nenhum serviço pago foi contratado, nenhum contato externo foi enviado e nenhum banco de outro projeto foi apagado. A stack foi adaptada à solicitação explícita de PHP.
