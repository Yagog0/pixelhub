# Central de controle

Entrada: `http://127.0.0.1:8081/caba%C3%A7o.php` (arquivo **cabaço.php**). Há uma entrada na raiz e outra em `public/`; as duas usam o mesmo controller e exigem sessão administrativa. O nome do arquivo não é uma proteção de acesso.

## Acesso

- Conta existente: `D:\php\php.exe scripts/admin.php email@exemplo.com`.
- Conta nova: `D:\php\php.exe scripts/invite-admin.php email@exemplo.com` gera um convite secreto de uso único, válido por 24 horas. Abra o link e escolha nome e senha. Nenhum e-mail é enviado.
- Gerar um novo convite invalida o anterior para esse e-mail. Registrar uma conta comum com o mesmo e-mail não a transforma em administradora.

O banco é o mesmo do site, fora da pasta pública. Nenhum dado existente foi apagado para adicionar o painel. A atualização cria tabelas complementares com `CREATE TABLE IF NOT EXISTS`.

## Abas

| Aba | O que permite |
| --- | --- |
| Visão geral | Pendências, publicados, contas, relatos, bibliotecas, downloads iniciados, conquistas e atividade de sete dias. |
| O que está chegando | Buscar rascunhos/envios, revisar direitos e builds, aprovar, solicitar ajustes e retirar. |
| Vitrine e catálogo | Destacar, ordenar por prioridade, definir coleção, ocultar ou devolver um jogo à seleção automática. |
| Página inicial | Editar títulos, descrições, três banners, artes, coleção de destino, banner inicial, quantidade de cards e ordem automática. |
| Contas | Consultar nomes, e-mails, papel, data de cadastro e quantidade de projetos; sem hashes de senha. |
| Denúncias | Filtrar abertas/analisadas e registrar a análise. |
| Histórico | Consultar decisões de publicação e alterações administrativas, com responsável e data. |

## Como o automático funciona

Projetos novos aparecem no painel, mas só entram no catálogo depois da revisão. Os aprovados entram automaticamente. Na seleção editorial, destaques manuais vêm primeiro, seguidos pela menor prioridade numérica e pela ordem automática escolhida (novos projetos ou nome). Os espaços restantes da home são preenchidos até o limite configurado. Ordenações explícitas do visitante por nome/novidades continuam prevalecendo.

Ocultar das vitrines remove o jogo de home, catálogo, busca, coleções e sorteio. **Não bloqueia a página por link ou a biblioteca.** Para bloquear novos downloads e retirar a página pública, use a retirada de publicação. A curadoria não altera builds nem aprova rascunhos.

A opção de esconder projetos fictícios afeta somente os seis exemplos do seed, não todos os jogos cujo tipo seja “Demo”. Restaurar a aparência padrão mantém a curadoria individual. Voltar um jogo ao automático mantém seu status de revisão.

## Métricas e atualização

O painel consulta o banco a cada abertura/atualização; não há polling em tempo real. Downloads contam inícios autorizados desde esta atualização, excluindo downloads administrativos para revisão. Não afirmam transferência concluída ou instalação. Bibliotecas contam entradas ainda existentes. Os agrupamentos diários usam UTC, indicado nas tabelas. Contagens gerais incluem os dados de demonstração quando aplicável.

## Código

- `app/Controllers/AdminController.php`: autorização, métricas, paginação e alterações de curadoria.
- `app/Controllers/AdminSetupController.php`: ativação por convite, consumo atômico e criação da conta.
- `app/Models/SiteSettings.php`: configuração padrão e leitura persistente.
- `views/admin/`: uma tela por aba e parciais compartilhados.
- `public/assets/css/admin.css`: componentes visuais do painel.
- `tests/admin_checks.py`: permissões, curadoria pública, configurações e convites.

As mutações editoriais passam por CSRF e autorização no servidor. Configurações e seus registros de auditoria são gravados na mesma transação. Textos são escapados no HTML; imagens e coleções usam listas permitidas. O convite administrativo armazena apenas hash da chave e é consumido sob transação para impedir reutilização.
