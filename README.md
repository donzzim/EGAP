# EGAP e Inventario Mobile

Repositorio com duas aplicacoes integradas:

- `egap`: sistema principal web em Laravel 11 + Filament 3, usado para administracao patrimonial, pedidos, almoxarifado, relatorios, agendamento e cadastros.
- `inventario-mobile`: aplicacao Expo/React Native usada em campo para consulta e conferencia patrimonial por setor.

O mobile consome a API Laravel em `/mobile-api`, autenticada com Laravel Sanctum. O Laravel continua sendo a fonte de verdade para autenticacao, escopo do usuario, regras de patrimonio e gravacoes de inventario.

## Estrutura Do Repositorio

```text
.
+-- egap/                 # Aplicacao Laravel/Filament principal
+-- inventario-mobile/    # Aplicacao Expo/React Native
+-- docs/                 # Documentos auxiliares e analises de fluxo
+-- .gitignore
`-- README.md             # Este documento
```

## Visao Geral Do Fluxo

```mermaid
graph TD
    A[Usuario abre o app mobile] --> B[Login em /mobile-api/login]
    B --> C[Laravel valida usuario local ou usuario EGAP]
    C --> D[UsersConnectionService resolve CPF, usuario EGAP e ultima lotacao]
    D --> E[Expo salva token e usuario no SecureStore]
    E --> F[Painel Patrimonio Mobile]
    F --> G[Consulta direta de patrimonio]
    F --> H[Listagem de bens do setor]
    F --> I[Conferencia de inventario]
    G --> J[BensController@show]
    H --> K[BensController@index]
    I --> L[ConferenciaBensService]
    L --> M[mat_patrimonio]
    L --> N[mat_inventario]
    L --> O[inv_atividades]
    L --> P[mat_itensinventario]
```

## Tecnologias

### Backend Desktop/API

- PHP `^8.2`
- Laravel `^11`
- Filament `3.3`
- Laravel Sanctum `^4`
- MySQL/MariaDB via conexao `egap`
- Vite para assets do Laravel
- PHPUnit e Laravel Pint para testes/formatacao

### Mobile

- Expo `~54`
- React `19`
- React Native `0.81`
- Expo Router `~6`
- TypeScript `~5.9`
- `expo-camera` para leitura de codigo de barras
- `expo-secure-store` para token e sessao local
- `react-native-gesture-handler` para menu lateral/gestos

## EGAP Desktop

O EGAP desktop fica no diretorio `egap` e registra um painel Filament em:

```text
/egap
```

Configuracao principal:

- Provider: `egap/app/Providers/Filament/EgapPanelProvider.php`
- Panel ID: `egap`
- Path: `/egap`
- Auth guard: `pessoa`
- Login customizado: `App\Filament\Auth\LoginApp`
- SPA habilitado via `->spa()`
- Auto-descoberta de Resources, Pages, Clusters e Widgets em `app/Filament`

### Modulos Principais Do Desktop

O painel Filament organiza as funcionalidades em grupos e clusters:

- Painel de Controle: dashboard administrativo com indicadores patrimoniais.
- Cadastro: setores, fornecedores, marcas, modelos, conta contabil, centro de custo, descricao resumida/detalhada, situacao do bem e unidades de medida.
- Patrimonio:
  - Bens moveis.
  - Incorporacao, transferencia, baixa, termos, validacao de termo, inventario, depreciacao, conciliacao e reavaliacao.
  - Bens imoveis.
  - Bens intangiveis.
- Pedidos:
  - Solicitacao de materiais.
  - Situacao de pedidos.
  - Relatorio e historico de pedidos.
  - Validacao de pedidos.
  - Atendimento patrimonial de pedidos.
  - Agendamento de entrega/recolhimento.
- Almoxarifado: notas fiscais, movimentacao de estoque, pedidos e situacoes.
- Agendamento: frota, equipes, regioes, transporte e solicitacoes.
- Processo: processos administrativos, materiais, tipos de documento e tipos de processo.
- Relatorios Gerais: relatorios TCE, bens moveis, bens imoveis, almoxarifado, pedidos e bens permanentes.
- Portal Transparencia: pagina de indicadores publicos/gerenciais.
- Administracao: usuarios, lotacoes e permissoes.

### Dashboard Desktop

Arquivo principal:

```text
egap/app/Filament/Pages/EgapDashboard.php
```

Widgets usados:

- `PatrimonioOverviewStats`
- `PatrimonioMoveisPorSituacaoChart`
- `PatrimonioMoveisPorAnoChart`
- `PatrimonioImoveisPorContaChart`
- `PatrimonioTopMateriaisValorTable`

Os indicadores sao calculados por:

```text
egap/app/Services/PatrimonioDashboardService.php
```

O dashboard aceita filtro por periodo de incorporacao, aplicado a bens moveis (`mat_patrimonio.DatadeIncorporacao`) e imoveis (`imo_imovel.data_incorporacao`).

### Fluxo Pedidos -> Patrimonio

O fluxo desktop mais importante integra pedidos, patrimonio e logistica:

1. Solicitante cria pedido de material permanente.
2. O pedido grava cabecalho em `ped_pedidos`.
3. Os itens sao gravados em `ped_itempedido`.
4. O historico do fluxo fica em `ped_fases`.
5. A area responsavel valida, invalida, cancela, suspende ou atualiza itens.
6. Patrimonio atende itens validados ou em analise selecionando bens fisicos disponiveis.
7. O atendimento gera termo em `mat_termos`.
8. O sistema cria/atualiza arquivo digital em `mat_arquivodigital`.
9. O bem e movimentado em `mat_transferencia`.
10. O cadastro do bem em `mat_patrimonio` e atualizado.
11. O item do pedido e atualizado.
12. O fluxo cria solicitacao logistica em `age_solicitacao` e vinculo em `age_materiais`.

Pontos de atencao desse fluxo:

- O setor de Patrimonio aparece em regras como ID `1239`.
- Depositos de bens disponiveis usam complementos especificos.
- Atendimento exige quantidade de bens selecionados igual a quantidade pendente.
- Historico funcional deve ser gravado em `ped_fases`.
- Algumas regras ainda dependem de IDs fixos e views legadas.

## Inventario Mobile

O mobile fica em `inventario-mobile` e usa Expo Router com rotas baseadas em arquivos.

### Estrutura Mobile

```text
inventario-mobile/
+-- app/
|   +-- _layout.tsx              # Layout raiz, ThemeProvider e GestureHandlerRootView
|   +-- index.tsx                # Login
|   `-- patrimonio/
|       +-- _layout.tsx          # Stack interno e menu lateral por gesto
|       +-- index.tsx            # Redirect para /patrimonio/principal
|       +-- principal.tsx        # Painel principal patrimonial
|       +-- bens.tsx             # Lista paginada de bens do setor
|       `-- conferencia.tsx      # Conferencia de inventario
+-- components/
|   +-- app-sidebar.tsx          # Menu lateral por modulo
|   +-- app-menu-button.tsx      # Botao de abertura do menu
|   `-- bottom-bar.tsx           # Navegacao inferior e logout
+-- src/
|   +-- api/                     # Cliente HTTP e contratos de API
|   +-- config/env.ts            # Variaveis EXPO_PUBLIC_*
|   +-- navigation/              # Direcao das animacoes do stack patrimonio
|   `-- storage/recentBens.ts    # Historico local de consultas
`-- app.json                     # Config Expo, plugins e permissoes
```

### Rotas Mobile

- `/`: tela de login.
- `/patrimonio`: redireciona para `/patrimonio/principal`.
- `/patrimonio/principal`: painel de resumo, consulta de patrimonio, leitura por camera e ultimas consultas.
- `/patrimonio/bens`: lista de bens vinculados ao setor do usuario.
- `/patrimonio/conferencia`: conferencia de inventario do setor.

### Navegacao Mobile

O app usa duas formas de navegacao:

- Barra inferior (`BottomBar`) com Inicio, Bens, Conferencia e Sair.
- Menu lateral (`AppSidebar`) com o grupo Patrimonio e placeholders para Almoxarifado, Processos e Relatorios.

O layout `app/patrimonio/_layout.tsx` tambem permite abrir o menu lateral por gesto de arrasto na borda esquerda.

As transicoes entre telas de patrimonio usam:

```text
inventario-mobile/src/navigation/patrimonioNavigation.ts
```

Esse helper define se a animacao deve vir da direita ou da esquerda conforme a ordem das rotas.

### Login E Sessao

Tela:

```text
inventario-mobile/app/index.tsx
```

Fluxo:

1. Ao abrir, o app verifica se existe sessao salva.
2. Se existir sessao, redireciona para `/patrimonio/principal`.
3. Se nao existir, mostra formulario de login.
4. O usuario informa login e senha.
5. O app chama `authApi.login`.
6. A API Laravel valida credenciais e retorna usuario + token.
7. O app grava token e usuario no `SecureStore`.
8. O usuario entra no modulo Patrimonio.

Arquivos envolvidos:

- `inventario-mobile/src/api/auth.ts`
- `inventario-mobile/src/api/client.ts`
- `inventario-mobile/src/config/env.ts`

Dados salvos localmente:

- `auth_token`
- `auth_user`
- `recent_bens:{userId}`

### Cliente HTTP Mobile

Arquivo:

```text
inventario-mobile/src/api/client.ts
```

Responsabilidades:

- Ler `ENV.API_URL`.
- Montar headers `Content-Type`, `Accept`, `Authorization` e `ngrok-skip-browser-warning`.
- Guardar/remover token via `SecureStore`.
- Converter respostas HTTP com erro em `ApiError`.
- Converter falhas de rede em `NetworkError`.

Variaveis de ambiente:

```text
EXPO_PUBLIC_API_URL=https://seu-ngrok-ou-host/mobile-api
EXPO_PUBLIC_USE_MOCK_API=false
```

Durante desenvolvimento com ngrok, se a URL mudar, atualize somente `.env.local` do mobile.

### Painel Principal Mobile

Arquivo:

```text
inventario-mobile/app/patrimonio/principal.tsx
```

Funcionalidades:

- Valida sessao com `/me`.
- Carrega dashboard mobile via `/dashboard`.
- Mostra dados do usuario, unidade e setor.
- Exibe indicadores de bens, valores e andamento de conferencia.
- Permite consulta manual de patrimonio.
- Permite leitura por camera usando `expo-camera`.
- Abre modal de detalhes do bem consultado.
- Mantem historico local das ultimas 5 consultas.
- Reconsulta um item do historico ao tocar nele.
- Atualiza historico e dashboard quando a tela volta ao foco.

Resumo dos dados exibidos no modal:

- Patrimonio atual e anterior.
- Tombo SMARAPD.
- Numero de serie.
- Descricao, marca, modelo e tipo.
- Situacao.
- Unidade, setor, complemento e andar.
- Valores, documentos e datas.
- Baixa e observacao quando existirem.

### Lista De Bens Do Setor

Arquivo:

```text
inventario-mobile/app/patrimonio/bens.tsx
```

Funcionalidades:

- Carrega bens do setor autenticado.
- Usa paginacao (`per_page = 30`).
- Permite busca por patrimonio, descricao, marca ou serie.
- Suporta pull-to-refresh.
- Carrega mais itens ao final da lista.
- Mostra total de bens retornado pela API.

API consumida:

```text
GET /mobile-api/bens?page=1&per_page=30&search=...
```

### Conferencia De Inventario

Arquivo:

```text
inventario-mobile/app/patrimonio/conferencia.tsx
```

Funcionalidades:

- Carrega inventario atual, atividade do setor, resumo e bens esperados.
- Mostra metricas: total, localizados, pendentes, nao localizados, divergentes, transferencia e manuais.
- Filtra lista por status.
- Valida leitura manual ou por camera.
- Permite confirmar localizacao.
- Permite registrar bem nao localizado com justificativa.
- Permite registrar divergencia com campos e observacao.
- Permite finalizar conferencia quando a API indicar `pode_finalizar`.
- Bloqueia acoes quando a atividade esta finalizada/bloqueada.

Status usados no mobile:

- `pendente`
- `localizado`
- `nao_localizado`
- `divergente`
- `em_transferencia`
- `cadastrado_manualmente`
- `registrado`

Resultados possiveis de leitura:

- `localizavel`
- `ja_conferido`
- `outro_setor`
- `nao_cadastrado`
- `situacao_nao_conferivel`
- `em_transferencia`
- `cadastrado_manualmente`

## API Mobile Laravel

Rotas em:

```text
egap/routes/api.php
```

Prefixo:

```text
/mobile-api
```

Rotas publicas:

| Metodo | Rota | Controller | Funcao |
|---|---|---|---|
| POST | `/mobile-api/login` | `MobileAuthController@login` | Autentica e gera token mobile |

Rotas protegidas por `auth:sanctum`:

| Metodo | Rota | Controller | Funcao |
|---|---|---|---|
| GET | `/mobile-api/me` | `MobileAuthController@me` | Valida sessao/token |
| POST | `/mobile-api/logout` | `MobileAuthController@logout` | Revoga token atual |
| GET | `/mobile-api/dashboard` | `BensController@dashboard` | Resumo patrimonial do setor |
| GET | `/mobile-api/bens` | `BensController@index` | Lista bens do setor |
| GET | `/mobile-api/bens/{numPatrimonio}` | `BensController@show` | Consulta patrimonio por codigo |
| GET | `/mobile-api/conferencia/atual` | `ConferenciaBensController@atual` | Inventario/atividade/resumo |
| GET | `/mobile-api/conferencia/bens` | `ConferenciaBensController@bens` | Bens esperados no setor |
| POST | `/mobile-api/conferencia/validar-leitura` | `ConferenciaBensController@validarLeitura` | Valida codigo lido |
| POST | `/mobile-api/conferencia/localizar` | `ConferenciaBensController@localizar` | Confirma localizacao |
| POST | `/mobile-api/conferencia/nao-localizados` | `ConferenciaBensController@naoLocalizados` | Registra nao localizado |
| POST | `/mobile-api/conferencia/divergencias` | `ConferenciaBensController@divergencias` | Registra divergencia |
| POST | `/mobile-api/conferencia/finalizar` | `ConferenciaBensController@finalizar` | Finaliza atividade do setor |

Comando util:

```powershell
cd egap
php artisan route:list --path=mobile-api
```

### Autenticacao Mobile

Controller:

```text
egap/app/Http/Controllers/Api/MobileAuthController.php
```

Fluxo:

1. Recebe `login` e `password`.
2. Tenta autenticar usuario local (`users`) por login, email ou CPF.
3. Se nao encontrar, tenta autenticar usuario EGAP (`UserEgap`) por username ou email.
4. Valida senha com `Hash::check`.
5. Usa `UsersConnectionService` para resolver vinculo mobile.
6. Gera token Sanctum com nome `mobile-app`.
7. Retorna usuario normalizado para o Expo.

Servico de vinculo:

```text
egap/app/Services/UsersConnectionService.php
```

Esse servico cruza:

- usuario local (`users`);
- CPF normalizado;
- `InfoUser`;
- usuario EGAP;
- ultima lotacao em `mat_lotacao`;
- unidade judiciaria e setor.

O Expo nunca envia setor/unidade como fonte de verdade. A API sempre resolve o escopo pelo token.

### Consulta De Bens

Controller:

```text
egap/app/Http/Controllers/Api/BensController.php
```

Regras principais:

- Situacoes elegiveis para bens do setor: `1`, `7`, `8`.
- A listagem `/bens` filtra por:
  - `UnidadeJudiciaria` do usuario;
  - `Setor` do usuario;
  - `SituacaoBem IN (1, 7, 8)`.
- A consulta direta `/bens/{numPatrimonio}` busca o bem no cadastro por codigo patrimonial, tombo, tombo SMARAPD ou patrimonio anterior, normalizando espacos, caracteres nao numericos e zeros a esquerda.
- O retorno da consulta direta inclui `scope.belongs_to_user_scope` e `scope.situacao_elegivel`.

Campos retornados para cada bem:

- `id`
- `patrimonio`
- `patrimonio_anterior`
- `tombo_smarapd`
- `num_tombo_smarapd`
- `numero_serie`
- `descricao`
- `descricao_resumida`
- `marca`
- `modelo`
- `tipo_bem`
- `estado_conservacao`
- `voltagem`
- `situacao`
- `unidade_judiciaria`
- `setor`
- `complemento_setor`
- `andar_setor`
- `valor_aquisicao`
- `valor`
- datas e documentos de incorporacao, cadastro, baixa, processo, empenho e liquidacao
- `observacao`

### Dashboard Mobile

Endpoint:

```text
GET /mobile-api/dashboard
```

Retorna:

- Escopo do usuario.
- Total de bens elegiveis do setor.
- Distribuicao por situacao patrimonial.
- Resumo financeiro:
  - valor de aquisicao;
  - valor atual;
  - bens sem valor;
  - quantidade avaliada.
- Dados de conferencia atual, quando existir inventario acessivel.

### Conferencia De Bens No Backend

Controller:

```text
egap/app/Http/Controllers/Api/ConferenciaBensController.php
```

Servico:

```text
egap/app/Services/Mobile/ConferenciaBensService.php
```

Responsabilidades do servico:

- Localizar inventario atual.
- Localizar ou criar atividade do setor.
- Listar bens esperados do setor.
- Associar cada bem ao item de inventario quando ja registrado.
- Validar leituras.
- Confirmar localizacao.
- Registrar nao localizado.
- Registrar divergencia.
- Recalcular resumo.
- Finalizar atividade do setor.

Status gravados/usados:

- `LOCALIZADO`
- `NAO LOCALIZADO` (no codigo legado pode aparecer com encoding antigo)
- `DIVERGENTE`
- `A INVENTARIAR`

Regras importantes:

- Escritas usam transacao na conexao `egap`.
- O backend impede duplicidade de item no inventario.
- Atividade finalizada ou com carga efetuada bloqueia edicoes.
- `mat_patrimonio.sit_inventario` e `mat_patrimonio.id_inventario` sao atualizados para manter compatibilidade com o legado.
- `mat_itensinventario` e a fonte principal do registro da conferencia atual.

## Modelo De Dados Relevante

Principais tabelas e models:

| Tabela | Model | Papel |
|---|---|---|
| `users` | `App\Models\User` | Usuario local Laravel/Sanctum |
| `jos_users` ou equivalente EGAP | `App\Models\UserEgap` | Usuario do sistema EGAP legado |
| `mat_lotacao` | `App\Models\Admin\Lotacao` | Unidade/setor vigente do usuario |
| `mat_patrimonio` | `BemMovel` | Cadastro principal de bens moveis |
| `mat_inventario` | `Inventario` | Ciclos de inventario |
| `inv_atividades` | `AtividadeInventario` | Status da conferencia por unidade/setor |
| `mat_itensinventario` | `ItemInventario` | Apontamentos da conferencia |
| `mat_transferencia` | `TransferenciaBemMovel` | Historico de movimentacao |
| `mat_termos` | `Termo` | Termos de responsabilidade |
| `mat_arquivodigital` | `ArquivoDigital` | Arquivos/validacao de termos |
| `mat_setores` | `Setores` | Unidades e setores |
| `mat_complementosetor` | `ComplementoSetor` | Complementos fisicos/logicos do setor |

## Configuracao E Execucao

### Backend Laravel

1. Entrar no projeto:

```powershell
cd egap
```

2. Instalar dependencias PHP:

```powershell
composer install
```

3. Instalar dependencias JS:

```powershell
npm install
```

4. Configurar `.env`:

```text
APP_NAME=EGAP
APP_ENV=local
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=patrimonio
DB_USERNAME=admin
DB_PASSWORD=admin
```

A conexao `egap` em `config/database.php` usa as variaveis `EGAP_DB_*` quando existirem; caso contrario, reaproveita `DB_*`.

5. Rodar migrations locais quando necessario:

```powershell
php artisan migrate
```

6. Subir Laravel:

```powershell
php artisan serve
```

7. Em outro terminal, subir assets:

```powershell
npm run dev
```

8. Acessar desktop:

```text
http://127.0.0.1:8000/egap
```

### API Mobile Com Ngrok

Se o Expo estiver em celular fisico, exponha o Laravel local:

```powershell
ngrok http 8000
```

Depois atualize:

```text
inventario-mobile/.env.local
```

Exemplo:

```text
EXPO_PUBLIC_API_URL=https://seu-subdominio.ngrok-free.dev/mobile-api
EXPO_PUBLIC_USE_MOCK_API=false
```

O cliente mobile envia o header `ngrok-skip-browser-warning: 1` automaticamente.

### Mobile Expo

1. Entrar no app:

```powershell
cd inventario-mobile
```

2. Instalar dependencias:

```powershell
npm install
```

3. Iniciar Expo:

```powershell
npm run start
```

Atalhos:

```powershell
npm run android
npm run ios
npm run web
```

No Windows, se o PowerShell bloquear `npm.ps1` ou `npx.ps1`, use:

```powershell
npm.cmd run start
npx.cmd expo start
```

## Validacao E Qualidade

### Backend

```powershell
cd egap
php artisan test
vendor\bin\pint --test
php -l app/Http/Controllers/Api/BensController.php
```

### Mobile

```powershell
cd inventario-mobile
npm.cmd run lint
npx.cmd tsc --noEmit
```

## Contratos Resumidos Da API Mobile

### Login

```http
POST /mobile-api/login
Content-Type: application/json
```

Body:

```json
{
  "login": "usuario",
  "password": "senha"
}
```

Resposta de sucesso:

```json
{
  "message": "Login realizado com sucesso.",
  "user": {
    "id": 1,
    "idEgap": 10,
    "login": "usuario",
    "name": "Nome do Usuario",
    "email": "usuario@example.com",
    "unidade_judiciaria": 100,
    "setor": 200,
    "token": "plain-text-sanctum-token"
  }
}
```

### Listagem De Bens

```http
GET /mobile-api/bens?page=1&per_page=30&search=notebook
Authorization: Bearer {token}
```

Resposta:

```json
{
  "scope": {
    "user_id": 1,
    "id_egap": 10,
    "setor": 200,
    "unidade_judiciaria": 100
  },
  "total": 123,
  "bens": [],
  "meta": {
    "current_page": 1,
    "per_page": 30,
    "total": 123,
    "last_page": 5,
    "from": 1,
    "to": 30,
    "has_more": true
  }
}
```

### Consulta Direta De Patrimonio

```http
GET /mobile-api/bens/{numPatrimonio}
Authorization: Bearer {token}
```

Resposta:

```json
{
  "bem": {
    "id": 1,
    "patrimonio": "12345",
    "descricao": "Descricao do bem"
  },
  "scope": {
    "belongs_to_user_scope": true,
    "situacao_elegivel": true
  }
}
```

### Conferencia Atual

```http
GET /mobile-api/conferencia/atual
Authorization: Bearer {token}
```

Resposta:

```json
{
  "inventario": {
    "id": 1,
    "numero": 1,
    "ano": 2026,
    "situacao": "Em andamento"
  },
  "atividade": {
    "id": 10,
    "unidade_judiciaria": 100,
    "setor": 200,
    "situacao": "Em andamento",
    "pode_editar": true,
    "pode_finalizar": false
  },
  "resumo": {
    "total": 100,
    "localizados": 70,
    "pendentes": 20,
    "nao_localizados": 5,
    "divergentes": 3,
    "outro_setor": 0,
    "em_transferencia": 2,
    "cadastrados_manualmente": 0,
    "pode_finalizar": false
  }
}
```

## Regras De Negocio Importantes

- O backend e a unica fonte confiavel para setor, unidade e usuario.
- O app mobile nao deve enviar setor/unidade para decidir escopo.
- Bens do setor usam `UnidadeJudiciaria`, `Setor` e `SituacaoBem IN (1, 7, 8)`.
- Consulta direta por patrimonio busca no cadastro geral e informa se pertence ao escopo do usuario.
- Confirmar localizacao cria/atualiza dados de inventario em transacao.
- Nao localizado exige justificativa.
- Divergencia exige observacao.
- Atividade finalizada bloqueia novas escritas.
- Finalizacao so deve ocorrer quando `pode_finalizar = true`.
- Historico local do mobile e conveniencia de interface; nao substitui auditoria no banco.

## Pontos De Atencao Tecnica

- Existem textos no projeto com encoding antigo; ao editar arquivos, preferir manter o padrao do arquivo e evitar introduzir mojibake novo.
- Algumas regras do desktop dependem de IDs fixos de setor/complemento/situacao. Documente qualquer novo uso desses IDs.
- O fluxo de atendimento de pedidos usa historico em `ped_fases`; novas automacoes devem preservar esse historico.
- A numeracao de termos baseada em `max(num_termo) + 1` merece cuidado em concorrencia.
- Views legadas podem conter filtros de negocio embutidos.
- O mobile usa ngrok em desenvolvimento; URL expirada causa erro de rede no app.

## Documentos Auxiliares

Arquivos em `docs/` complementam este README:

- `docs/orientacoes_login_sessao_laravel.txt`
- `docs/orientacoes_login_sessao_expo.txt`
- `docs/orientacoes_conferencia_bens.md`
- `docs/bens_documentacao.md`
- `docs/relatorio-fluxo-patrimonio-pedidos-egap.md`

## Checklist De Onboarding

1. Subir MySQL com a base EGAP acessivel.
2. Configurar `egap/.env`.
3. Rodar `composer install` e `npm install` no `egap`.
4. Subir Laravel com `php artisan serve`.
5. Conferir `/egap`.
6. Conferir `php artisan route:list --path=mobile-api`.
7. Subir ngrok apontando para a porta Laravel.
8. Configurar `inventario-mobile/.env.local`.
9. Rodar `npm install` no mobile.
10. Rodar `npm.cmd run start`.
11. Fazer login no app.
12. Testar painel, consulta de patrimonio, bens do setor e conferencia.

## Manutencao Recomendada

- Extrair IDs fixos para configuracao ou tabela de parametros.
- Consolidar regras de transferencia/termo em services de dominio.
- Criar testes de integracao para a API mobile.
- Criar testes de fluxo para conferencia: leitura, localizar, nao localizado, divergencia e finalizar.
- Melhorar tratamento de encoding em arquivos herdados.
- Evoluir o mobile para modo offline apenas depois de estabilizar regras de sincronizacao e conflito.
- Atualizar este README sempre que uma rota, regra de inventario ou fluxo de pedidos/patrimonio mudar.
