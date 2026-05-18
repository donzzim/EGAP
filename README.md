# 🗂️ EGAP e Inventário Mobile

> Repositório com duas aplicações integradas para gestão e conferência patrimonial.

| Aplicação | Tecnologia | Finalidade |
|---|---|---|
| `egap` | Laravel 11 + Filament 3 | Administração patrimonial, pedidos, almoxarifado, relatórios e agendamento |
| `inventario-mobile` | Expo / React Native | Consulta e conferência patrimonial em campo |

O mobile consome a API Laravel em `/mobile-api`, autenticada com **Laravel Sanctum**. O Laravel é a fonte de verdade para autenticação, escopo do usuário, regras de patrimônio e gravações de inventário.

---

## 📁 Estrutura do Repositório

```text
.
├── egap/                 # Aplicação Laravel/Filament principal
├── inventario-mobile/    # Aplicação Expo/React Native
├── docs/                 # Documentos auxiliares e análises de fluxo
├── .gitignore
└── README.md             # Este documento
```

---

## 🔄 Visão Geral do Fluxo

```mermaid
graph TD
    A[Usuário abre o app mobile] --> B[Login em /mobile-api/login]
    B --> C[Laravel valida usuário local ou usuário EGAP]
    C --> D[UsersConnectionService resolve CPF e última lotação]
    D --> E[Expo salva token e usuário no SecureStore]
    E --> F[Painel Patrimônio Mobile]
    F --> G[Consulta direta de patrimônio]
    F --> H[Listagem de bens do setor]
    F --> I[Conferência de inventário]
    G --> J[BensController - show]
    H --> K[BensController - index]
    I --> L[ConferenciaBensService]
    L --> M[(mat_patrimonio)]
    L --> N[(mat_inventario)]
    L --> O[(inv_atividades)]
    L --> P[(mat_itensinventario)]
```

---

## 🛠️ Tecnologias

### Backend Desktop / API

| Tecnologia | Versão |
|---|---|
| PHP | `^8.2` |
| Laravel | `^11` |
| Filament | `3.3` |
| Laravel Sanctum | `^4` |
| Banco de dados | MySQL / MariaDB (conexão `egap`) |
| Assets | Vite |
| Qualidade | PHPUnit + Laravel Pint |

### Mobile

| Tecnologia | Versão |
|---|---|
| Expo | `~54` |
| React | `19` |
| React Native | `0.81` |
| Expo Router | `~6` |
| TypeScript | `~5.9` |
| `expo-camera` | Leitura de código de barras |
| `expo-secure-store` | Token e sessão local |
| `react-native-gesture-handler` | Menu lateral / gestos |

---

## 🖥️ EGAP Desktop

O EGAP desktop fica no diretório `egap` e registra um painel Filament em `/egap`.

**Configuração principal:**

| Parâmetro | Valor |
|---|---|
| Provider | `egap/app/Providers/Filament/EgapPanelProvider.php` |
| Panel ID | `egap` |
| Path | `/egap` |
| Auth guard | `pessoa` |
| Login customizado | `App\Filament\Auth\LoginApp` |
| SPA | habilitado via `->spa()` |

### Módulos Principais do Desktop

| Módulo | Funcionalidades |
|---|---|
| **Painel de Controle** | Dashboard administrativo com indicadores patrimoniais |
| **Cadastro** | Setores, fornecedores, marcas, modelos, conta contábil, centro de custo, situação do bem e unidades de medida |
| **Patrimônio** | Bens móveis, imóveis e intangíveis; incorporação, transferência, baixa, termos, inventário, depreciação, conciliação e reavaliação |
| **Pedidos** | Solicitação, validação, atendimento patrimonial e agendamento de entrega/recolhimento |
| **Almoxarifado** | Notas fiscais, movimentação de estoque, pedidos e situações |
| **Agendamento** | Frota, equipes, regiões, transporte e solicitações |
| **Processo** | Processos administrativos, materiais e tipos de documento/processo |
| **Relatórios Gerais** | Relatórios TCE, bens móveis, bens imóveis, almoxarifado, pedidos e bens permanentes |
| **Portal Transparência** | Página de indicadores públicos/gerenciais |
| **Administração** | Usuários, lotações e permissões |

### Dashboard Desktop

**Arquivo principal:**

```text
egap/app/Filament/Pages/EgapDashboard.php
```

**Widgets utilizados:**

- `PatrimonioOverviewStats`
- `PatrimonioMoveisPorSituacaoChart`
- `PatrimonioMoveisPorAnoChart`
- `PatrimonioImoveisPorContaChart`
- `PatrimonioTopMateriaisValorTable`

Os indicadores são calculados por:

```text
egap/app/Services/PatrimonioDashboardService.php
```

O dashboard aceita filtro por período de incorporação, aplicado a bens móveis (`mat_patrimonio.DatadeIncorporacao`) e imóveis (`imo_imovel.data_incorporacao`).

### Fluxo Pedidos → Patrimônio

O fluxo desktop mais importante integra pedidos, patrimônio e logística:

```mermaid
graph TD
    P1[Solicitante cria pedido] --> P2[Cabeçalho gravado em ped_pedidos]
    P2 --> P3[Itens gravados em ped_itempedido]
    P3 --> P4[Histórico em ped_fases]
    P4 --> P5{Área responsável}
    P5 -->|Valida| P6[Patrimônio seleciona bens físicos]
    P5 -->|Invalida / Cancela| P9[Encerrado]
    P6 --> P7[Gera termo em mat_termos]
    P7 --> P8[Arquivo digital em mat_arquivodigital]
    P8 --> P10[Movimentação em mat_transferencia]
    P10 --> P11[Atualiza mat_patrimonio]
    P11 --> P12[Atualiza item do pedido]
    P12 --> P13[Cria solicitação logística em age_solicitacao]
    P13 --> P14[Vínculo em age_materiais]
```

> ⚠️ **Pontos de atenção:**
> - O setor de Patrimônio aparece em regras como ID `1239`.
> - Depósitos de bens disponíveis usam complementos específicos.
> - Atendimento exige quantidade de bens selecionados igual à quantidade pendente.
> - Histórico funcional deve ser gravado em `ped_fases`.
> - Algumas regras ainda dependem de IDs fixos e views legadas.

---

## 📱 Inventário Mobile

O mobile fica em `inventario-mobile` e usa Expo Router com rotas baseadas em arquivos.

### Estrutura Mobile

```text
inventario-mobile/
├── app/
│   ├── _layout.tsx              # Layout raiz, ThemeProvider e GestureHandlerRootView
│   ├── index.tsx                # Login
│   └── patrimonio/
│       ├── _layout.tsx          # Stack interno e menu lateral por gesto
│       ├── index.tsx            # Redirect para /patrimonio/principal
│       ├── principal.tsx        # Painel principal patrimonial
│       ├── bens.tsx             # Lista paginada de bens do setor
│       └── conferencia.tsx      # Conferência de inventário
├── components/
│   ├── app-sidebar.tsx          # Menu lateral por módulo
│   ├── app-menu-button.tsx      # Botão de abertura do menu
│   └── bottom-bar.tsx           # Navegação inferior e logout
├── src/
│   ├── api/                     # Cliente HTTP e contratos de API
│   ├── config/env.ts            # Variáveis EXPO_PUBLIC_*
│   ├── navigation/              # Direção das animações do stack patrimônio
│   └── storage/recentBens.ts    # Histórico local de consultas
└── app.json                     # Config Expo, plugins e permissões
```

### Rotas Mobile

| Rota | Descrição |
|---|---|
| `/` | Tela de login |
| `/patrimonio` | Redireciona para `/patrimonio/principal` |
| `/patrimonio/principal` | Painel de resumo, consulta de patrimônio, leitura por câmera e últimas consultas |
| `/patrimonio/bens` | Lista de bens vinculados ao setor do usuário |
| `/patrimonio/conferencia` | Conferência de inventário do setor |

### Navegação Mobile

O app usa duas formas de navegação:

- **Barra inferior** (`BottomBar`) — Início, Bens, Conferência e Sair.
- **Menu lateral** (`AppSidebar`) — grupo Patrimônio e placeholders para Almoxarifado, Processos e Relatórios.

O layout `app/patrimonio/_layout.tsx` também permite abrir o menu lateral por gesto de arrasto na borda esquerda.

As transições entre telas de patrimônio usam:

```text
inventario-mobile/src/navigation/patrimonioNavigation.ts
```

### Login e Sessão

**Arquivo:** `inventario-mobile/app/index.tsx`

```mermaid
graph TD
    L1[App abre] --> L2{Sessão salva?}
    L2 -->|Sim| L3[Redireciona para /patrimonio/principal]
    L2 -->|Não| L4[Exibe formulário de login]
    L4 --> L5[Usuário informa login e senha]
    L5 --> L6[authApi.login]
    L6 --> L7[API Laravel valida credenciais]
    L7 --> L8[Retorna usuário + token]
    L8 --> L9[Grava token e usuário no SecureStore]
    L9 --> L3
```

**Arquivos envolvidos:**

- `inventario-mobile/src/api/auth.ts`
- `inventario-mobile/src/api/client.ts`
- `inventario-mobile/src/config/env.ts`

**Dados salvos localmente:**

- `auth_token`
- `auth_user`
- `recent_bens:{userId}`

### Cliente HTTP Mobile

**Arquivo:** `inventario-mobile/src/api/client.ts`

**Responsabilidades:**

- Ler `ENV.API_URL`.
- Montar headers `Content-Type`, `Accept`, `Authorization` e `ngrok-skip-browser-warning`.
- Guardar/remover token via `SecureStore`.
- Converter respostas HTTP com erro em `ApiError`.
- Converter falhas de rede em `NetworkError`.

**Variáveis de ambiente:**

```text
EXPO_PUBLIC_API_URL=https://seu-ngrok-ou-host/mobile-api
EXPO_PUBLIC_USE_MOCK_API=false
```

> Durante desenvolvimento com ngrok, se a URL mudar, atualize somente `.env.local` do mobile.

### Painel Principal Mobile

**Arquivo:** `inventario-mobile/app/patrimonio/principal.tsx`

**Funcionalidades:**

- Valida sessão com `/me`.
- Carrega dashboard mobile via `/dashboard`.
- Mostra dados do usuário, unidade e setor.
- Exibe indicadores de bens, valores e andamento de conferência.
- Permite consulta manual de patrimônio.
- Permite leitura por câmera usando `expo-camera`.
- Abre modal de detalhes do bem consultado.
- Mantém histórico local das últimas 5 consultas.
- Reconsulta um item do histórico ao tocar nele.
- Atualiza histórico e dashboard quando a tela volta ao foco.

**Dados exibidos no modal do bem:**

- Patrimônio atual e anterior, Tombo SMARAPD, número de série.
- Descrição, marca, modelo e tipo.
- Situação.
- Unidade, setor, complemento e andar.
- Valores, documentos e datas.
- Baixa e observação (quando existirem).

### Lista de Bens do Setor

**Arquivo:** `inventario-mobile/app/patrimonio/bens.tsx`

**Funcionalidades:**

- Carrega bens do setor autenticado.
- Usa paginação (`per_page = 30`).
- Permite busca por patrimônio, descrição, marca ou série.
- Suporta pull-to-refresh.
- Carrega mais itens ao final da lista.
- Mostra total de bens retornado pela API.

**API consumida:**

```text
GET /mobile-api/bens?page=1&per_page=30&search=...
```

### Conferência de Inventário

**Arquivo:** `inventario-mobile/app/patrimonio/conferencia.tsx`

**Funcionalidades:**

- Carrega inventário atual, atividade do setor, resumo e bens esperados.
- Mostra métricas: total, localizados, pendentes, não localizados, divergentes, transferência e manuais.
- Filtra lista por status.
- Valida leitura manual ou por câmera.
- Permite confirmar localização.
- Permite registrar bem não localizado com justificativa.
- Permite registrar divergência com campos e observação.
- Permite finalizar conferência quando a API indicar `pode_finalizar`.
- Bloqueia ações quando a atividade está finalizada/bloqueada.

**Status usados no mobile:**

| Status | Descrição |
|---|---|
| `pendente` | Bem ainda não conferido |
| `localizado` | Bem encontrado e confirmado |
| `nao_localizado` | Bem não encontrado (requer justificativa) |
| `divergente` | Bem com divergência (requer observação) |
| `em_transferencia` | Bem em processo de transferência |
| `cadastrado_manualmente` | Registro manual |
| `registrado` | Registrado na conferência |

**Resultados possíveis de leitura:**

- `localizavel`, `ja_conferido`, `outro_setor`, `nao_cadastrado`, `situacao_nao_conferivel`, `em_transferencia`, `cadastrado_manualmente`

---

## 🌐 API Mobile Laravel

**Arquivo de rotas:** `egap/routes/api.php` | **Prefixo:** `/mobile-api`

### Rotas Públicas

| Método | Rota | Controller | Função |
|---|---|---|---|
| `POST` | `/mobile-api/login` | `MobileAuthController` | Autentica e gera token mobile |

### Rotas Protegidas (`auth:sanctum`)

| Método | Rota | Controller | Função |
|---|---|---|---|
| `GET` | `/mobile-api/me` | `MobileAuthController` | Valida sessão/token |
| `POST` | `/mobile-api/logout` | `MobileAuthController` | Revoga token atual |
| `GET` | `/mobile-api/dashboard` | `BensController` | Resumo patrimonial do setor |
| `GET` | `/mobile-api/bens` | `BensController` | Lista bens do setor |
| `GET` | `/mobile-api/bens/{numPatrimonio}` | `BensController` | Consulta patrimônio por código |
| `GET` | `/mobile-api/conferencia/atual` | `ConferenciaBensController` | Inventário/atividade/resumo |
| `GET` | `/mobile-api/conferencia/bens` | `ConferenciaBensController` | Bens esperados no setor |
| `POST` | `/mobile-api/conferencia/validar-leitura` | `ConferenciaBensController` | Valida código lido |
| `POST` | `/mobile-api/conferencia/localizar` | `ConferenciaBensController` | Confirma localização |
| `POST` | `/mobile-api/conferencia/nao-localizados` | `ConferenciaBensController` | Registra não localizado |
| `POST` | `/mobile-api/conferencia/divergencias` | `ConferenciaBensController` | Registra divergência |
| `POST` | `/mobile-api/conferencia/finalizar` | `ConferenciaBensController` | Finaliza atividade do setor |

**Comando útil:**

```powershell
cd egap
php artisan route:list --path=mobile-api
```

### Autenticação Mobile

**Controller:** `egap/app/Http/Controllers/Api/MobileAuthController.php`

**Fluxo:**

1. Recebe `login` e `password`.
2. Tenta autenticar usuário local (`users`) por login, email ou CPF.
3. Se não encontrar, tenta autenticar usuário EGAP (`UserEgap`) por username ou email.
4. Valida senha com `Hash::check`.
5. Usa `UsersConnectionService` para resolver vínculo mobile.
6. Gera token Sanctum com nome `mobile-app`.
7. Retorna usuário normalizado para o Expo.

**Serviço de vínculo:** `egap/app/Services/UsersConnectionService.php`

Esse serviço cruza: usuário local (`users`), CPF normalizado, `InfoUser`, usuário EGAP, última lotação em `mat_lotacao`, unidade judiciária e setor.

> O Expo **nunca** envia setor/unidade como fonte de verdade. A API sempre resolve o escopo pelo token.

### Consulta de Bens

**Controller:** `egap/app/Http/Controllers/Api/BensController.php`

**Regras principais:**

- Situações elegíveis para bens do setor: `1`, `7`, `8`.
- A listagem `/bens` filtra por `UnidadeJudiciaria`, `Setor` e `SituacaoBem IN (1, 7, 8)`.
- A consulta direta `/bens/{numPatrimonio}` busca por código patrimonial, tombo, tombo SMARAPD ou patrimônio anterior, normalizando espaços, caracteres não numéricos e zeros à esquerda.
- O retorno inclui `scope.belongs_to_user_scope` e `scope.situacao_elegivel`.

**Campos retornados por bem:** `id`, `patrimonio`, `patrimonio_anterior`, `tombo_smarapd`, `num_tombo_smarapd`, `numero_serie`, `descricao`, `descricao_resumida`, `marca`, `modelo`, `tipo_bem`, `estado_conservacao`, `voltagem`, `situacao`, `unidade_judiciaria`, `setor`, `complemento_setor`, `andar_setor`, `valor_aquisicao`, `valor`, datas e documentos de incorporação/cadastro/baixa/processo/empenho/liquidação, `observacao`.

### Dashboard Mobile

**Endpoint:** `GET /mobile-api/dashboard`

**Retorna:**

- Escopo do usuário.
- Total de bens elegíveis do setor.
- Distribuição por situação patrimonial.
- Resumo financeiro: valor de aquisição, valor atual, bens sem valor e quantidade avaliada.
- Dados de conferência atual, quando existir inventário acessível.

### Conferência de Bens no Backend

**Controller:** `egap/app/Http/Controllers/Api/ConferenciaBensController.php`

**Serviço:** `egap/app/Services/Mobile/ConferenciaBensService.php`

**Responsabilidades do serviço:** localizar inventário atual; localizar ou criar atividade do setor; listar bens esperados; associar cada bem ao item de inventário; validar leituras; confirmar localização; registrar não localizado; registrar divergência; recalcular resumo; finalizar atividade.

**Status gravados:** `LOCALIZADO`, `NAO LOCALIZADO` (encoding legado), `DIVERGENTE`, `A INVENTARIAR`.

> ⚠️ **Regras importantes:**
> - Escritas usam transação na conexão `egap`.
> - O backend impede duplicidade de item no inventário.
> - Atividade finalizada ou com carga efetuada bloqueia edições.
> - `mat_patrimonio.sit_inventario` e `mat_patrimonio.id_inventario` são atualizados para compatibilidade com o legado.
> - `mat_itensinventario` é a fonte principal do registro da conferência atual.

---

## 🗃️ Modelo de Dados Relevante

| Tabela | Model | Papel |
|---|---|---|
| `users` | `App\Models\User` | Usuário local Laravel/Sanctum |
| `jos_users` / equiv. EGAP | `App\Models\UserEgap` | Usuário do sistema EGAP legado |
| `mat_lotacao` | `App\Models\Admin\Lotacao` | Unidade/setor vigente do usuário |
| `mat_patrimonio` | `BemMovel` | Cadastro principal de bens móveis |
| `mat_inventario` | `Inventario` | Ciclos de inventário |
| `inv_atividades` | `AtividadeInventario` | Status da conferência por unidade/setor |
| `mat_itensinventario` | `ItemInventario` | Apontamentos da conferência |
| `mat_transferencia` | `TransferenciaBemMovel` | Histórico de movimentação |
| `mat_termos` | `Termo` | Termos de responsabilidade |
| `mat_arquivodigital` | `ArquivoDigital` | Arquivos/validação de termos |
| `mat_setores` | `Setores` | Unidades e setores |
| `mat_complementosetor` | `ComplementoSetor` | Complementos físicos/lógicos do setor |

---

## ⚙️ Configuração e Execução

### Backend Laravel

```powershell
# 1. Entrar no projeto
cd egap

# 2. Instalar dependências PHP
composer install

# 3. Instalar dependências JS
npm install

# 4. Configurar .env
APP_NAME=EGAP
APP_ENV=local
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=patrimonio
DB_USERNAME=admin
DB_PASSWORD=admin

# 5. Rodar migrations (quando necessário)
php artisan migrate

# 6. Subir Laravel
php artisan serve

# 7. Em outro terminal, subir assets
npm run dev
```

> Acesse o desktop em: `http://127.0.0.1:8000/egap`

> A conexão `egap` em `config/database.php` usa as variáveis `EGAP_DB_*` quando existirem; caso contrário, reutiliza `DB_*`.

### API Mobile com Ngrok

Se o Expo estiver em celular físico, exponha o Laravel local:

```powershell
ngrok http 8000
```

Depois atualize `inventario-mobile/.env.local`:

```text
EXPO_PUBLIC_API_URL=https://seu-subdominio.ngrok-free.dev/mobile-api
EXPO_PUBLIC_USE_MOCK_API=false
```

> O cliente mobile envia o header `ngrok-skip-browser-warning: 1` automaticamente.

### Mobile Expo

```powershell
# 1. Entrar no app
cd inventario-mobile

# 2. Instalar dependências
npm install

# 3. Iniciar Expo
npm run start

# Atalhos por plataforma
npm run android
npm run ios
npm run web
```

> **Windows:** se o PowerShell bloquear `npm.ps1` ou `npx.ps1`, use:
> ```powershell
> npm.cmd run start
> npx.cmd expo start
> ```

---

## ✅ Validação e Qualidade

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

---

## 📋 Contratos Resumidos da API Mobile

### Login

```http
POST /mobile-api/login
Content-Type: application/json
```

```json
{
  "login": "usuario",
  "password": "senha"
}
```

**Resposta de sucesso:**

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

### Listagem de Bens

```http
GET /mobile-api/bens?page=1&per_page=30&search=notebook
Authorization: Bearer {token}
```

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

### Consulta Direta de Patrimônio

```http
GET /mobile-api/bens/{numPatrimonio}
Authorization: Bearer {token}
```

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

### Conferência Atual

```http
GET /mobile-api/conferencia/atual
Authorization: Bearer {token}
```

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

---

## 📏 Regras de Negócio Importantes

> ⚠️ Leia com atenção antes de qualquer modificação no backend ou mobile.

- O backend é a **única fonte confiável** para setor, unidade e usuário.
- O app mobile **não deve enviar** setor/unidade para decidir escopo.
- Bens do setor usam `UnidadeJudiciaria`, `Setor` e `SituacaoBem IN (1, 7, 8)`.
- Consulta direta por patrimônio busca no cadastro geral e informa se pertence ao escopo do usuário.
- Confirmar localização cria/atualiza dados de inventário em transação.
- Não localizado **exige justificativa**.
- Divergência **exige observação**.
- Atividade finalizada **bloqueia** novas escritas.
- Finalização só deve ocorrer quando `pode_finalizar = true`.
- Histórico local do mobile é conveniência de interface; **não substitui** auditoria no banco.

---

## 🔍 Pontos de Atenção Técnica

- Existem textos no projeto com encoding antigo; ao editar arquivos, manter o padrão do arquivo e evitar introduzir mojibake novo.
- Algumas regras do desktop dependem de **IDs fixos** de setor/complemento/situação. Documente qualquer novo uso desses IDs.
- O fluxo de atendimento de pedidos usa histórico em `ped_fases`; novas automações devem preservar esse histórico.
- A numeração de termos baseada em `max(num_termo) + 1` merece cuidado em **concorrência**.
- Views legadas podem conter filtros de negócio embutidos.
- O mobile usa ngrok em desenvolvimento; URL expirada causa erro de rede no app.

---

## 📚 Documentos Auxiliares

Arquivos em `docs/` complementam este README:

| Arquivo | Conteúdo |
|---|---|
| `docs/orientacoes_login_sessao_laravel.txt` | Orientações de login e sessão no Laravel |
| `docs/orientacoes_login_sessao_expo.txt` | Orientações de login e sessão no Expo |
| `docs/orientacoes_conferencia_bens.md` | Guia de conferência de bens |
| `docs/bens_documentacao.md` | Documentação de bens |
| `docs/relatorio-fluxo-patrimonio-pedidos-egap.md` | Relatório do fluxo patrimônio–pedidos |

---

## 🚀 Checklist de Onboarding

- [ ] Subir MySQL com a base EGAP acessível.
- [ ] Configurar `egap/.env`.
- [ ] Rodar `composer install` e `npm install` no `egap`.
- [ ] Subir Laravel com `php artisan serve`.
- [ ] Conferir `/egap`.
- [ ] Conferir `php artisan route:list --path=mobile-api`.
- [ ] Subir ngrok apontando para a porta Laravel.
- [ ] Configurar `inventario-mobile/.env.local`.
- [ ] Rodar `npm install` no mobile.
- [ ] Rodar `npm.cmd run start`.
- [ ] Fazer login no app.
- [ ] Testar painel, consulta de patrimônio, bens do setor e conferência.

---

## 🔧 Manutenção Recomendada

- Extrair IDs fixos para configuração ou tabela de parâmetros.
- Consolidar regras de transferência/termo em services de domínio.
- Criar testes de integração para a API mobile.
- Criar testes de fluxo para conferência: leitura, localizar, não localizado, divergência e finalizar.
- Melhorar tratamento de encoding em arquivos herdados.
- Evoluir o mobile para modo offline apenas depois de estabilizar regras de sincronização e conflito.
- Atualizar este README sempre que uma rota, regra de inventário ou fluxo de pedidos/patrimônio mudar.
