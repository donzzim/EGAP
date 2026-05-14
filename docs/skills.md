# skills.md — Diretrizes do Projeto EGap Mobile

Este arquivo serve como referência permanente para o Codex durante o desenvolvimento do projeto **EGap Mobile**.

O objetivo é manter documentadas as principais regras, decisões, fluxos, entidades, arquitetura e critérios de desenvolvimento da aplicação mobile vinculada ao sistema EGap.

---

## 1. Contexto funcional do sistema

### Contexto atual do EGap

Hoje, o controle dos bens no setor é feito manualmente pelo sistema web.

O fluxo atual funciona assim:

1. O usuário realiza login no sistema.
2. Após o login, algumas informações do usuário ficam armazenadas na sessão, como:
   - login;
   - senha;
   - setor vinculado ao usuário;
   - demais credenciais necessárias para operação no sistema.

3. Ao acessar a funcionalidade **“Bens no Setor”**, o backend retorna todos os bens patrimoniais localizados no mesmo setor do usuário logado.

4. A tela web exibe uma listagem com os bens vinculados ao setor do usuário.

5. A partir dessa listagem, o usuário pode executar algumas ações:
   - Confirmar localização do bem;
   - Marcar bem como não localizado;
   - Incluir bem no setor;
   - Transferir bem para outro setor.

6. O problema atual é que esse processo é muito manual. O usuário precisa olhar item por item na listagem e conferir fisicamente os códigos patrimoniais dos bens localizados no setor.

### Nova ideia

Criar uma aplicação mobile para facilitar esse processo.

O app deve permitir que o usuário faça login, visualize seu setor atual e use a câmera do celular para escanear o código de barras/plaqueta patrimonial de um bem.

Após a leitura do código, o app deverá consultar a API e identificar:

1. Se o bem pertence ao setor do usuário;
2. Se o bem está cadastrado no sistema;
3. Se o bem já foi confirmado anteriormente;
4. Se o bem pertence a outro setor;
5. Se o bem não está localizado na listagem esperada;
6. Se existe alguma pendência, divergência ou situação patrimonial especial.

Com base no resultado da leitura, o app deverá permitir ações como:

- Confirmar localização;
- Marcar como não localizado;
- Solicitar inclusão no setor;
- Solicitar transferência;
- Registrar observação;
- Exibir detalhes do bem;
- Exibir histórico básico da conferência atual.

---

## 2. Objetivo do projeto mobile

### Objetivo principal

Criar uma aplicação mobile para conferência patrimonial dos bens vinculados ao setor do usuário, usando leitura de código de barras.

### Objetivos específicos

1. Autenticar o usuário usando credenciais compatíveis com o EGap.
2. Identificar o setor vinculado ao usuário autenticado.
3. Buscar a lista de bens esperados no setor.
4. Permitir leitura de código de barras pela câmera.
5. Validar o bem lido contra a listagem esperada.
6. Atualizar o status da conferência do bem.
7. Permitir ações de controle patrimonial:
   - confirmar localização;
   - marcar como não localizado;
   - registrar divergência;
   - solicitar inclusão;
   - solicitar transferência.
8. Exibir resumo da conferência:
   - total de bens esperados;
   - bens confirmados;
   - bens pendentes;
   - bens não localizados;
   - bens divergentes;
   - bens lidos que pertencem a outro setor.
9. Preparar a aplicação para funcionar futuramente com modo offline ou sincronização posterior, mesmo que isso não seja implementado na primeira versão.

---

## 3. Stack técnica sugerida

### Mobile

- React Native;
- Expo;
- TypeScript;
- Expo Router ou React Navigation;
- Axios para comunicação HTTP;
- React Hook Form para formulários;
- Zod para validação;
- AsyncStorage ou SecureStore para armazenamento local de token;
- Expo Camera ou Expo Barcode Scanner para leitura de código de barras;
- Context API ou Zustand para gerenciamento de estado inicial;
- Estrutura modular baseada em features.

### Backend esperado

- API REST vinculada ao EGap;
- A API será responsável por consultar o banco de dados do EGap;
- O app mobile não deve acessar o banco diretamente;
- Toda regra de negócio deve ser centralizada no backend sempre que possível.

### Banco de dados

- O app usará indiretamente o mesmo banco do EGap;
- O banco contém tabelas relacionadas a patrimônio, setores, usuários, situação do bem, transferências e localização patrimonial;
- As tabelas reais serão integradas posteriormente, então o app deve começar usando services, interfaces e mocks bem organizados.

### Ambiente

- IDE: WebStorm;
- Sistema operacional provável: Windows;
- Gerenciador: npm;
- Projeto deve ser fácil de executar localmente.

---

## 4. Diretrizes de arquitetura

O projeto deve seguir uma arquitetura limpa, modular e fácil de manter.

Evite criar tudo dentro de uma única pasta ou arquivo.

### Estrutura inicial sugerida

```txt
src/
  app/
    navigation/
    routes/
  assets/
  components/
    common/
    layout/
    feedback/
  features/
    auth/
      screens/
      services/
      hooks/
      types/
    patrimonio/
      screens/
      services/
      hooks/
      types/
      components/
    conferencia/
      screens/
      services/
      hooks/
      types/
      components/
  services/
    api/
      apiClient.ts
      endpoints.ts
  storage/
    authStorage.ts
  types/
    api.ts
    common.ts
  utils/
    formatters.ts
    validators.ts
  constants/
    colors.ts
    routes.ts
  config/
    env.ts
```

### Regras importantes

1. Separar telas, componentes, services, hooks e types.
2. Não misturar regra de negócio diretamente dentro dos componentes visuais.
3. Criar services para comunicação com a API.
4. Criar types TypeScript para todas as entidades principais.
5. Criar componentes reutilizáveis para botões, cards, inputs, estados vazios e feedbacks.
6. Preparar o projeto para crescimento futuro.
7. Não usar nomes genéricos demais quando o domínio permitir nomes mais claros.
8. Priorizar legibilidade e manutenção.
9. Manter a aplicação preparada para autenticação via token.
10. Não implementar acesso direto ao banco de dados pelo app.

---

## 5. Entidades principais do domínio

Considere inicialmente as seguintes entidades do domínio.

### Usuário

- id;
- nome;
- login;
- setorId;
- setorNome;
- token de autenticação.

### Setor

- id;
- nome;
- unidade;
- complemento, se existir.

### Bem patrimonial

- id;
- codigoPatrimonial;
- descricao;
- situacao;
- setorAtualId;
- setorAtualNome;
- unidade;
- complemento;
- dataIncorporacao;
- valorAquisicao;
- statusConferencia.

### Conferência

- id;
- setorId;
- usuarioId;
- dataInicio;
- dataFim;
- status;
- totalBens;
- totalConfirmados;
- totalPendentes;
- totalNaoLocalizados;
- totalDivergentes.

### Item de conferência

- id;
- conferenciaId;
- bemId;
- codigoPatrimonial;
- status;
- dataLeitura;
- observacao.

### Possíveis status do item de conferência

- pendente;
- localizado;
- nao_localizado;
- divergente;
- outro_setor;
- nao_cadastrado;
- aguardando_transferencia;
- aguardando_inclusao.

Esses modelos podem ser inicialmente representados como types/interfaces TypeScript.

---

## 6. Fluxo inicial esperado no app

### 1. Tela de Login

- Usuário informa login e senha.
- App envia credenciais para API.
- API retorna token, dados do usuário e setor vinculado.
- App salva token com segurança.
- App redireciona para a Home.

### 2. Tela Home/Dashboard

- Exibe nome do usuário.
- Exibe setor vinculado.
- Exibe resumo da conferência atual.
- Botão para iniciar ou continuar conferência.
- Botão para acessar lista de bens do setor.

### 3. Tela de Bens no Setor

- Lista os bens esperados no setor do usuário.
- Permite busca por código ou descrição.
- Exibe status de cada bem:
  - pendente;
  - confirmado;
  - não localizado;
  - divergente.

### 4. Tela de Scanner

- Abre a câmera.
- Lê código de barras/plaqueta patrimonial.
- Após leitura, consulta a API ou service mock.
- Exibe resultado da validação.

### 5. Tela ou Modal de Resultado da Leitura

Caso o bem pertença ao setor:

- mostrar dados do bem;
- permitir confirmar localização.

Caso o bem pertença a outro setor:

- exibir aviso;
- permitir solicitar transferência.

Caso o bem não exista:

- exibir aviso;
- permitir registrar ocorrência.

Caso o bem já tenha sido confirmado:

- exibir informação e evitar duplicidade.

Caso haja divergência:

- permitir registrar observação.

### 6. Tela de Resumo da Conferência

- Mostrar totais da conferência.
- Mostrar progresso.
- Listar pendências.
- Permitir finalizar conferência futuramente.

---

## 7. Regras de UX/UI

A interface deve ser simples, institucional, limpa e eficiente.

Como o público será composto por usuários administrativos, a prioridade é clareza, velocidade e redução de erro operacional.

### Diretrizes visuais

1. Usar layout limpo e profissional.
2. Priorizar botões grandes e fáceis de tocar.
3. Usar cores de status:
   - verde para localizado/confirmado;
   - amarelo/laranja para pendente ou atenção;
   - vermelho para não localizado/divergente;
   - azul/cinza para informações neutras.
4. Evitar telas poluídas.
5. Exibir feedback claro após cada leitura.
6. Usar cards para informações de bens.
7. Usar ícones quando fizer sentido, mas sem exagero.
8. O scanner deve ser acessível rapidamente.
9. A Home deve deixar claro em qual setor o usuário está operando.
10. O usuário deve conseguir entender rapidamente quantos bens ainda faltam conferir.

Crie uma base visual consistente, mas não perca tempo com excesso de estilização neste primeiro momento.

---

## 8. API esperada futuramente

A aplicação deve ser preparada para consumir endpoints semelhantes aos abaixo.

Ainda não implemente chamadas reais se a API não existir. Crie services preparados e, se necessário, mocks temporários.

### Autenticação

#### `POST /mobile-api/auth/login`

Autentica o usuário.

Recebe:

- login;
- senha.

Retorna:

- token;
- usuário;
- setor.

#### `GET /mobile-api/auth/me`

Retorna dados do usuário autenticado.

---

### Patrimônio

#### `GET /mobile-api/setores/{setorId}/bens`

Retorna bens vinculados ao setor do usuário.

---

### Conferências

#### `POST /mobile-api/conferencias`

Cria uma nova conferência para o setor.

#### `GET /mobile-api/conferencias/atual`

Retorna a conferência em andamento do usuário/setor.

#### `POST /mobile-api/conferencias/{conferenciaId}/scan`

Recebe o código patrimonial lido.

Valida o bem.

Retorna status da leitura.

#### `POST /mobile-api/conferencias/{conferenciaId}/itens/{itemId}/confirmar`

Confirma localização do bem.

#### `POST /mobile-api/conferencias/{conferenciaId}/itens/{itemId}/nao-localizado`

Marca bem como não localizado.

#### `POST /mobile-api/conferencias/{conferenciaId}/itens/{itemId}/observacao`

Registra observação.

#### `POST /mobile-api/conferencias/{conferenciaId}/finalizar`

Finaliza a conferência.

---

### Solicitações patrimoniais

#### `POST /mobile-api/bens/{bemId}/solicitar-transferencia`

Solicita transferência de bem localizado em outro setor.

#### `POST /mobile-api/bens/solicitar-inclusao`

Solicita inclusão de bem não esperado no setor.

A estrutura do app deve facilitar a substituição dos mocks por chamadas reais via Axios futuramente.

---

## 9. Primeira tarefa de código

Comece implementando a base inicial do projeto mobile.

### Tarefa inicial

1. Criar ou organizar a estrutura de pastas do projeto.
2. Configurar TypeScript.
3. Criar um `apiClient` usando Axios.
4. Criar tipos TypeScript para:
   - User;
   - Setor;
   - BemPatrimonial;
   - Conferencia;
   - ItemConferencia;
   - ScanResult.
5. Criar mocks temporários para bens patrimoniais.
6. Criar services iniciais:
   - authService;
   - patrimonioService;
   - conferenciaService.
7. Criar uma tela de Login simples.
8. Criar uma tela Home/Dashboard.
9. Criar uma tela de listagem de bens no setor.
10. Criar uma tela de Scanner inicial com estrutura preparada para leitura de código de barras.
11. Criar uma navegação inicial entre essas telas.
12. Criar componentes reutilizáveis básicos:
    - AppButton;
    - AppInput;
    - StatusBadge;
    - BemCard;
    - ScreenContainer;
    - EmptyState;
    - LoadingState.

Neste primeiro momento, use dados mockados onde a API ainda não existir.

### Regras para geração de código

Ao gerar código:

- Use TypeScript.
- Use nomes claros.
- Evite arquivos gigantes.
- Não misture regra de negócio com componente visual.
- Comente apenas o necessário.
- Priorize código limpo e manutenível.
- Sempre que criar um arquivo novo, informe o caminho completo.
- Se modificar arquivos existentes, mostre o arquivo completo.

---

## 10. Critérios de aceite da primeira etapa

A primeira etapa será considerada concluída quando:

1. O app conseguir abrir sem erro.
2. Existir uma tela de Login funcional com mock.
3. Ao fazer login mockado, o usuário for levado para a Home.
4. A Home exibir:
   - nome do usuário;
   - setor vinculado;
   - resumo da conferência.
5. A tela de Bens no Setor listar bens mockados.
6. Cada bem exibir:
   - código patrimonial;
   - descrição;
   - setor;
   - situação;
   - status de conferência.
7. A tela de Scanner existir e estar preparada para integrar leitura real de código de barras.
8. O projeto estiver organizado por features.
9. Os services estiverem separados da UI.
10. Os types principais estiverem definidos.
11. O código estiver pronto para substituição dos mocks por API real futuramente.

---

## 11. Orientação de comportamento para o Codex

Durante o desenvolvimento, siga estas regras:

1. Antes de implementar, analise a estrutura atual do projeto.
2. Não apague arquivos importantes sem necessidade.
3. Não crie soluções improvisadas que dificultem manutenção.
4. Sempre prefira uma estrutura escalável.
5. Se houver conflito entre simplicidade e arquitetura exagerada, escolha uma solução intermediária.
6. O projeto ainda está no início, então priorize fundação bem feita.
7. Não implemente autenticação real sem endpoint definido.
8. Não implemente acesso direto ao banco.
9. Não crie regras patrimoniais definitivas no frontend.
10. As regras de negócio críticas devem ficar preparadas para serem delegadas à API.
11. O frontend deve apenas consumir respostas da API e refletir estados.
12. Sempre pense no contexto do EGap e do controle patrimonial.
13. Sempre que fizer suposições, deixe claro no comentário ou na resposta.
14. Prefira código funcional, simples e preparado para evolução.

---

## 12. Prompt complementar — depois que a base estiver criada

Use este segundo prompt depois que o Codex montar a estrutura inicial.

```text
Agora que a base inicial do projeto mobile foi criada, quero evoluir o fluxo de conferência patrimonial.

Implemente o fluxo mockado de leitura de código patrimonial.

Objetivo:

Quando o usuário informar ou escanear um código patrimonial, o sistema deve validar esse código contra a lista mockada de bens do setor e retornar um resultado claro.

Regras de validação mockadas:

1. Se o código existir e o bem pertencer ao setor do usuário:
   - status: localizado;
   - mensagem: Bem localizado no setor;
   - permitir confirmar localização.

2. Se o código existir, mas pertencer a outro setor:
   - status: outro_setor;
   - mensagem: Bem pertence a outro setor;
   - permitir solicitar transferência.

3. Se o código não existir:
   - status: nao_cadastrado;
   - mensagem: Bem não encontrado no sistema;
   - permitir registrar ocorrência.

4. Se o bem já estiver confirmado:
   - status: ja_confirmado;
   - mensagem: Bem já confirmado anteriormente;
   - não duplicar confirmação.

5. Se o bem estiver na lista do setor, mas ainda pendente:
   - permitir confirmar.

Implemente:

1. Um método em conferenciaService para validar o código lido.
2. Um estado local para atualizar o status dos bens após confirmação.
3. Um modal ou tela de resultado da leitura.
4. Feedback visual claro para cada tipo de resultado.
5. Botão para confirmar localização.
6. Botão para voltar ao scanner.
7. Atualização do resumo da conferência na Home.

Mantenha os dados mockados por enquanto, mas organize tudo como se futuramente fosse substituído por API real.
```

---

## 13. Ordem recomendada de execução

A ordem recomendada para iniciar o projeto é:

1. Criar o projeto Expo com TypeScript.
2. Configurar a estrutura de pastas.
3. Criar os types do domínio.
4. Criar mocks de usuário, setor, bens e conferência.
5. Criar services mockados.
6. Criar navegação.
7. Criar Login mockado.
8. Criar Home/Dashboard.
9. Criar listagem de bens.
10. Criar tela de scanner.
11. Criar validação mockada do código lido.
12. Depois integrar com API real do EGap.

---

## 14. Comandos iniciais sugeridos

### Criar projeto Expo com TypeScript

```bash
npx create-expo-app egap-mobile -t expo-template-blank-typescript
```

### Entrar na pasta do projeto

```bash
cd egap-mobile
```

### Instalar dependências iniciais

```bash
npm install axios zod react-hook-form zustand
```

### Instalar navegação com React Navigation

```bash
npm install @react-navigation/native
npm install @react-navigation/native-stack
npx expo install react-native-screens react-native-safe-area-context
```

### Instalar suporte à câmera/scanner

```bash
npx expo install expo-camera
```

---

## 15. Recomendação técnica inicial

A stack recomendada para a primeira versão é:

- Expo;
- React Native;
- TypeScript;
- React Navigation;
- Axios;
- Zustand;
- Expo Camera.

Essa combinação é simples, produtiva e suficiente para a primeira versão do app de conferência patrimonial do EGap.

A prioridade inicial deve ser criar uma base funcional e bem organizada, não uma solução final completa.
