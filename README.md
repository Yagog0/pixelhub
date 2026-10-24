# PixelHub

![PHP](https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-003B57?logo=sqlite&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?logo=javascript&logoColor=black)

Plataforma web de jogos com catálogo, biblioteca pessoal, conquistas e moderação. Implementada em **PHP e SQLite**, com organização MVC simples e interface em português. Pasta de origem: **milionario**.

## Funcionalidades

- Cadastro, login, perfil, biblioteca e favoritos.
- Catálogo com busca, filtros, coleções e sorteio de jogos.
- Painel do criador para cadastrar projetos e enviar builds para revisão.
- Moderação, denúncias, auditoria e gestão da vitrine.
- Conquistas de exploração e integração por API.
- Minijogo original **Órbita de Papel**, com teclado e toque.

## Executar localmente

Requisitos: **PHP 8.2+** com PDO SQLite, mbstring e fileinfo. Não precisa de Composer, Node.js ou MySQL para executar a aplicação.

Na raiz do projeto:

```sh
php -d upload_max_filesize=20M -d post_max_size=22M -S 127.0.0.1:8081 -t public
```

Abra **http://127.0.0.1:8081**. A estrutura SQLite e os dados demonstrativos são criados automaticamente. As artes SVG são locais.

Para conceder acesso administrativo a uma conta já cadastrada, no mesmo ambiente do servidor:

```sh
php scripts/admin.php seu-email@exemplo.com
```

Não existe senha administrativa padrão. Consulte [o guia do painel](docs/PAINEL-ADMIN.md) para os fluxos de administração e ativação.

## Estrutura

| Pasta | Responsabilidade |
| --- | --- |
| `app/` | Controllers, models, banco e funções compartilhadas |
| `config/` | Configurações e limites |
| `database/` | Schema e dados demonstrativos |
| `public/` | Entrada web, estilos, scripts e artes |
| `views/` | Telas e componentes PHP |
| `scripts/` | Inicialização e ferramentas administrativas |
| `tests/` | Verificações HTTP e de navegador |
| `docs/` | Arquitetura, integração e painel |

## Documentação

- [Arquitetura](docs/ARQUITETURA.md)
- [Painel administrativo](docs/PAINEL-ADMIN.md)
- [Integração](docs/INTEGRACAO.md)
- [Configuração detalhada e notas do ambiente original](docs/CONFIGURACAO-LOCAL.md)

## Desenvolvimento

```sh
python tests/smoke.py --php php
```

Node.js é opcional para formatação e verificações de navegador. As instruções detalhadas estão na referência de configuração.

## Dados e estágio do projeto

Banco, sessões e builds permanecem fora da pasta pública. `PIXELHUB_STORAGE` permite definir o diretório persistente de dados; use o mesmo valor no servidor e nas ferramentas administrativas.

Os projetos e estúdios iniciais são fictícios; somente Órbita de Papel inclui uma experiência jogável. Launcher, pagamentos e e-mail de recuperação ainda não fazem parte desta versão. A raiz contém uma ponte `index.html` para o ambiente local original; a execução acima usa diretamente a pasta `public/`.

## Autor

[Yago Maba · @Yagog0](https://github.com/Yagog0)
