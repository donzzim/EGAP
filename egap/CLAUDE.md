# Como o Claude Code funciona neste projeto

Este arquivo explica, em português, como a ferramenta **Claude Code** (o assistente de IA usado no terminal) atua durante o desenvolvimento deste projeto Laravel/Filament.

## O que é o Claude Code

Claude Code é uma CLI (interface de linha de comando) da Anthropic que conecta o modelo de linguagem Claude diretamente ao ambiente de desenvolvimento. Diferente de um chat comum, ele tem acesso a **ferramentas** que permitem ler, escrever e executar código de verdade neste repositório.

## Ferramentas principais usadas em programação

- **Leitura de arquivos** (`Read`): lê o conteúdo de qualquer arquivo do projeto para entender o código antes de alterá-lo.
- **Busca** (`Glob`, `Grep`): localiza arquivos por padrão de nome (ex.: `app/Filament/Resources/**/*.php`) ou por conteúdo (ex.: uma função, classe ou string específica).
- **Edição** (`Edit`, `Write`): faz alterações pontuais em arquivos existentes ou cria novos arquivos quando necessário.
- **Execução de comandos** (`Bash`/`PowerShell`): roda comandos como `php artisan migrate`, `composer install`, `npm run dev`, `vendor\bin\pint --test`, `php artisan test`, além de comandos `git`.
- **Sub-agentes** (`Agent`): para tarefas grandes ou de pesquisa (ex.: "onde está definida a regra X?"), pode delegar a busca para um agente especializado, mantendo a conversa principal mais enxuta.
- **Skills**: comandos especializados (`/code-review`, `/verify`, `/security-review`, etc.) que aplicam fluxos de trabalho prontos, como revisão de código ou verificação de que uma mudança realmente funciona.

## Como ele aborda uma tarefa de código

1. **Entende o pedido** no contexto do projeto (ex.: "ajustar o Resource de Bens Móveis" é interpretado como uma mudança em `app/Filament/Resources/Patrimonio/BensMoveis/`, não como um pedido genérico).
2. **Explora o código relevante** antes de editar — lê os arquivos envolvidos, entende convenções já usadas (nomes de conexões de banco, padrões dos Resources do Filament, etc.).
3. **Faz a menor mudança necessária** para resolver o problema, evitando refatorações ou abstrações não solicitadas.
4. **Verifica o resultado**, quando possível, rodando testes (`php artisan test`), lint (`vendor\bin\pint --test`) ou exercitando a funcionalidade.
5. **Reporta de forma direta** o que mudou e o que falta, sem inflar a resposta com explicações desnecessárias.

## Segurança e ações sensíveis

Antes de ações difíceis de reverter ou que afetam sistemas compartilhados — `git push`, `git reset --hard`, apagar arquivos, alterar migrations já aplicadas — o Claude Code pede confirmação ao usuário. Ações locais e reversíveis (editar arquivo, rodar teste) são feitas livremente.

## Particularidades deste projeto

- Este é um projeto **Laravel 11 + Filament 3**, com autenticação via **Sanctum** para o app mobile.
- Existem duas conexões de banco relevantes: `egap` (tabelas legadas `mat_*`, `ped_*`, `alm_*`, `jos_users`) e `emes` (tabelas de autenticação/tokens). O Claude precisa respeitar qual conexão cada model/consulta usa.
- Painel administrativo em `/egap`, API mobile em `/mobile-api` (rotas em `routes/api.php`, protegidas por `auth:sanctum`).
- Comandos de verificação úteis: `php artisan route:list --path=mobile-api`, `php artisan test`, `vendor\bin\pint --test`.

## Memória entre conversas

O Claude Code mantém uma memória persistente (fora deste repositório) com preferências de trabalho, contexto de projeto e feedback já dado pelo usuário, para não repetir perguntas já respondidas em sessões anteriores.
