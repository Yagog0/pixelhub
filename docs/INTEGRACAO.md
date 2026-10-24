# Integração de conquistas

O criador salva o rascunho e abre **Editar → Conquistas** para definir uma chave estável, título, descrição e segredo. Reenviar a mesma chave atualiza a definição enquanto o projeto ainda é rascunho. Depois do envio para revisão, as definições ficam bloqueadas junto com a versão.

Depois da publicação, o jogador adiciona o jogo à biblioteca. Na página do jogo, **Conectar conquistas ao meu jogo** gera uma chave pessoal de uma hora. A chave é exibida uma vez; gerar outra revoga a anterior. Não coloque uma chave administrativa dentro de uma build.

Requisição do jogo, via HTTP:

```http
POST /index.php?page=api-unlock
Authorization: Bearer CHAVE_PESSOAL_DO_JOGADOR
Content-Type: application/json

{"key":"primeira-vitoria"}
```

Resposta:

```json
{"ok":true,"new_unlock":true,"title":"A primeira de muitas"}
```

Repetir a requisição retorna `new_unlock: false`. A chave autoriza somente o jogador e o projeto associados, exige o jogo na biblioteca e expira em uma hora. Projetos retirados não aceitam eventos. Erros: 401 para token inválido/expirado; 422 para conquista inexistente. O caminho acima é do servidor com `public/` como raiz; no Apache em subpasta, acrescente `/Projetos/milionario/`.

Conquistas reportadas por um cliente local podem ser manipuladas pelo jogador. A interface as identifica como **informadas pelo jogo**, sem validação competitiva, prêmios ou rankings. A integração não inspeciona ROMs e não funciona automaticamente com qualquer jogo. O minijogo original tem sua própria integração de demonstração por sessão e CSRF.

Em produção, substituir a geração manual de chaves por autorização no navegador, implementar limites dedicados, consentimentos, renovação e revogação pelo jogador. Não foi implementado um SDK de engine nem validação antitrapaça.
