# Orientações da Conferência de Bens

Este documento define as regras de negócio para a principal funcionalidade do Inventário Mobile: conferência de bens patrimoniais do setor do usuário, baseada no arquivo legado `docs/bens.php` e na estrutura atual Laravel + Expo.

## 1. Estado atual analisado

### Backend Laravel

- A API mobile está em `routes/api.php`, prefixada por `/mobile-api`.
- A autenticação mobile usa Sanctum nas rotas protegidas.
- Já existem endpoints:
  - `POST /mobile-api/login`
  - `GET /mobile-api/me`
  - `POST /mobile-api/logout`
  - `GET /mobile-api/bens`
  - `GET /mobile-api/bens/{numPatrimonio}`
- `MobileAuthController` autentica por usuário local ou usuário EGap e retorna token.
- `UsersConnectionService` resolve o usuário mobile a partir de CPF, `info_user` e última lotação.
- `BensController@index` lista bens pelo setor e unidade do usuário autenticado.
- `BensController@show` consulta um bem pelo `NumPatrimonio`.
- Os models principais já existem:
  - `BemMovel` -> `mat_patrimonio`
  - `ItemInventario` -> `mat_itensinventario`
  - `Inventario` -> `mat_inventario`
  - `AtividadeInventario` -> `inv_atividades`
  - `TransferenciaBemMovel` -> `mat_transferencia`
  - `Termo` -> `mat_termos`

### Banco EGap

Base analisada: conexão Laravel `egap`, banco `egap`.

Tabelas centrais:

- `mat_patrimonio`: cadastro principal dos bens móveis.
- `mat_itensinventario`: registros dos bens conferidos/localizados/não localizados em um inventário.
- `mat_inventario`: ciclos de inventário.
- `inv_atividades`: situação do inventário por unidade/setor.
- `mat_transferencia`: histórico de movimentações dos bens.
- `mat_termos`: termos de responsabilidade.
- `mat_arquivodigital`: anexos/validação de termos.
- `mat_setores`: unidades e setores.
- `mat_complementosetor`: complementos de setor.

Volumes observados no banco atual:

- `mat_patrimonio`: 193.702 registros.
- `mat_itensinventario`: 573.044 registros.
- `mat_inventario`: 7 registros.
- `inv_atividades`: 3.142 registros.
- `mat_transferencia`: 1.229.033 registros.
- `mat_termos`: 168.886 registros.
- `mat_arquivodigital`: 128.715 registros.

Situações patrimoniais relevantes em `mat_situacao`:

- `1`: Ativo.
- `7`: Baixa em andamento.
- `8`: Cadastrado Manualmente.
- `9`: Incorporado.
- `12`: Consumo Durável.

O `bens.php` usa principalmente `SituacaoBem IN (1, 7, 8)` para a relação de bens do setor.

### Frontend Expo

- O app usa Expo Router.
- `app/index.tsx` contém login real integrado à API.
- `app/principal.tsx` contém sessão, logout, scanner com `expo-camera`, entrada manual de patrimônio e consulta de bem.
- `app/bens.tsx` lista bens retornados por `/mobile-api/bens`.
- `app/conferencia.tsx` ainda é uma tela base/placeholder para receber as regras da conferência.
- `src/api/client.ts` centraliza as requisições, token no `SecureStore` e header do ngrok.
- `src/api/auth.ts` controla login, sessão, validação e logout.
- `src/api/bens.ts` normaliza respostas de bens e consulta por patrimônio.
- `src/storage/recentBens.ts` guarda as últimas consultas localmente.

## 2. Objetivo da funcionalidade

A conferência de bens deve permitir que o usuário autenticado confira, pelo celular, os bens esperados no seu setor durante um inventário.

O app deve:

- carregar o inventário atual;
- carregar os bens esperados para o setor do usuário;
- ler ou digitar o código patrimonial;
- validar o bem no backend;
- informar se o bem está correto, já conferido, em outro setor, não cadastrado ou em situação especial;
- permitir confirmar localização;
- permitir registrar bem não localizado;
- permitir registrar divergências;
- exibir progresso da conferência;
- permitir finalizar a atividade do setor somente quando não houver pendências impeditivas.

## 3. Regra de escopo do usuário

O Expo nunca deve enviar como verdade final o setor, unidade ou usuário responsável pela operação.

O Laravel deve sempre resolver o escopo a partir do token:

- usuário Laravel autenticado;
- usuário EGap vinculado (`idEgap`);
- última lotação do usuário;
- `unidade_judiciaria`;
- `setor`;
- futuramente, complemento do setor quando a regra exigir.

Se o usuário não tiver vínculo, unidade ou setor válidos, a API deve retornar erro e bloquear a conferência.

## 4. Inventário atual

No legado, `bens.php` obtém o inventário assim:

- usa `$_SESSION['idinventario']` quando existe;
- caso contrário, usa o último inventário finalizado;
- usa `$_SESSION['inventarioonline']` para distinguir inventário em execução de inventário finalizado.

Na API Laravel, essa regra deve virar um serviço explícito, sem depender de sessão PHP:

- localizar o inventário ativo ou vigente para a conferência mobile;
- retornar `id_inventario`, número, ano, datas e situação;
- localizar a atividade do setor em `inv_atividades`;
- se não existir atividade para o setor, tratar como `Aberto`, seguindo o comportamento do legado;
- bloquear alterações quando a atividade do setor estiver finalizada.

Regra recomendada:

- inventário com atividade `Aberto` ou `Em andamento`: permite conferência;
- atividade `Finalizado` ou `carga efetuada`: permite consulta, mas bloqueia confirmação, não localizado e edição;
- inventário inexistente: retornar erro claro para o app.

## 5. Bens esperados no setor

A lista base deve vir de `mat_patrimonio`.

Filtro principal herdado do legado:

- `mat_patrimonio.Setor = setor do usuário`;
- preferencialmente também validar `UnidadeJudiciaria = unidade do usuário`;
- `SituacaoBem IN (1, 7, 8)`;
- ordenar por descrição, marca, modelo e número patrimonial.

O retorno deve incluir:

- `id`;
- `NumPatrimonio`;
- `NumerodePatAnterior`;
- `Descricao`;
- descrição resumida/detalhada;
- marca;
- modelo;
- número de série;
- estado de conservação;
- unidade;
- setor;
- complemento;
- andar;
- situação patrimonial;
- `sit_inventario`;
- `id_inventario`;
- indicador se já existe registro em `mat_itensinventario` para o inventário atual.

Importante:

- O legado calcula total geral com `SituacaoBem IN (1, 7)`, mas lista também `8`.
- Para a conferência mobile, o total exibido deve separar:
  - total de bens elegíveis;
  - total conferido;
  - total pendente;
  - total não localizado;
  - total em transferência;
  - total cadastrado manualmente.

## 6. Bens pendentes

Um bem é pendente quando:

- pertence ao setor do usuário;
- está em situação elegível;
- ainda não possui registro em `mat_itensinventario` para o `id_inventario` atual e o setor atual.

O legado também considera pendência a partir de `sit_inventario`:

- `A INVENTARIAR`: pendente;
- `EM TRANSFERÊNCIA`: pendente quando `SituacaoBem != 7`;
- `NÃO LOCALIZADO`: pendência operacional, mas já possui apontamento;
- `LOCALIZADO`: conferido.

Regra para o Laravel:

- usar `mat_itensinventario` como fonte principal da conferência atual;
- usar `mat_patrimonio.sit_inventario` como estado auxiliar legado;
- evitar confiar apenas em `sit_inventario`, pois ele representa estado agregado e histórico.

## 7. Validação de uma leitura

Ao ler ou digitar um patrimônio, o app deve chamar o backend para validar. A regra não deve ser decidida apenas no Expo.

Entrada mínima:

- código patrimonial lido;
- opcionalmente `id_inventario`, se a API já tiver retornado a conferência atual.

O backend deve normalizar o código:

- remover espaços;
- aceitar leitura com zeros à esquerda quando aplicável;
- buscar por `NumPatrimonio`;
- quando necessário, permitir busca complementar por `TomboSmarapd`, `NumTomboSmarapd` ou patrimônio anterior, mas a primeira versão pode manter `NumPatrimonio` como regra principal.

Resultados esperados:

- `localizavel`: bem existe, pertence ao setor do usuário, está em situação elegível e ainda não foi conferido neste inventário.
- `ja_conferido`: já existe registro em `mat_itensinventario` para o mesmo inventário, setor e bem.
- `outro_setor`: bem existe, mas `Setor` é diferente do setor do usuário.
- `nao_cadastrado`: nenhum bem encontrado para o código informado.
- `situacao_nao_conferivel`: bem existe, mas `SituacaoBem` não está entre as situações elegíveis.
- `em_transferencia`: bem está em transferência e exige tratamento específico.
- `cadastrado_manualmente`: bem está com `SituacaoBem = 8` e pode exigir verificação de termo válido.

O retorno deve sempre trazer uma mensagem pronta para a interface e os dados essenciais do bem quando existir.

## 8. Confirmar localização

Confirmar localização é a ação principal.

Pré-condições:

- usuário autenticado;
- usuário com setor válido;
- inventário atual válido;
- atividade do setor não finalizada;
- bem existente;
- bem pertence ao setor do usuário;
- bem em situação elegível;
- bem ainda não confirmado no inventário atual.

Operações no backend:

- abrir transação na conexão `egap`;
- bloquear/validar duplicidade do item no inventário;
- criar registro em `mat_itensinventario`;
- copiar para o item os dados atuais do bem:
  - `id_bem`;
  - `unidades`;
  - `num_patrimonio`;
  - `num_patrimonioantigo`;
  - `num_serie`;
  - `descricao_resumida`;
  - `descricao_detalhada`;
  - `marca`;
  - `modelo`;
  - `setor`;
  - `id_inventario`;
  - `estado_conservacao`;
  - `id_complementosetor`;
  - dados equivalentes `_egap`, quando aplicável;
  - `situacao = LOCALIZADO`;
  - `atualizado_por = idEgap do usuário`;
  - `date_time = now()`;
- atualizar `mat_patrimonio.sit_inventario = LOCALIZADO`;
- atualizar `mat_patrimonio.id_inventario = id_inventario`;
- recalcular ou atualizar `inv_atividades.qtde_inventariada`.

Essa ação deve ser idempotente:

- se o mesmo bem já foi confirmado, retornar `ja_conferido`, sem criar duplicidade.

## 9. Registrar bem não localizado

No legado, a ação "Bem Não Localizado" exige seleção de bens e justificativa.

Na API mobile:

- o usuário deve selecionar um ou mais bens pendentes;
- a justificativa é obrigatória;
- a atividade do setor não pode estar finalizada.

Operações esperadas:

- criar ou atualizar apontamento em `mat_itensinventario`;
- registrar `situacao = NÃO LOCALIZADO`;
- registrar observação/justificativa;
- atualizar `mat_patrimonio.sit_inventario = NÃO LOCALIZADO` quando a regra atual permitir;
- auditar usuário e data.

O Expo deve bloquear envio sem justificativa.

## 10. Registrar divergência

Divergência ocorre quando o bem foi encontrado fisicamente, mas há diferença em dados cadastrais.

Exemplos:

- número de série diferente;
- marca/modelo divergente;
- complemento do setor incorreto;
- estado de conservação diferente;
- descrição incompatível;
- bem no setor fisicamente, mas sistema indica outro setor.

Na primeira versão mobile, divergência deve ser registrada como ocorrência, sem alterar automaticamente dados mestres do patrimônio.

Operações esperadas:

- criar apontamento associado ao inventário e ao bem;
- registrar campos divergentes e observação;
- retornar status `divergente`;
- deixar alteração definitiva do cadastro para fluxo administrativo no Laravel/Filament ou etapa posterior.

## 11. Bem encontrado em outro setor

Se o bem existir, mas estiver vinculado a outro setor:

- o app deve informar o setor atual do sistema;
- permitir registrar ocorrência;
- não deve transferir automaticamente;
- não deve confirmar como localizado no setor do usuário sem regra administrativa definida.

Transferência deve ser tratada como fluxo separado, pois no legado envolve:

- setor destino;
- complemento;
- justificativa obrigatória;
- geração de termo;
- assinatura eletrônica;
- `mat_transferencia`;
- `mat_termos`;
- `mat_arquivodigital`.

## 12. Cadastrado manualmente e termo válido

No legado, há tratamento especial para `SituacaoBem = 8`.

Regra observada:

- quando o bem está cadastrado manualmente e não possui termos válidos, ele pode não contar como pendência normal de inventário.

Para a API:

- retornar indicador `cadastrado_manualmente`;
- consultar histórico em `mat_transferencia`, `mat_termos` e `mat_arquivodigital` quando a decisão depender de termo válido;
- não esconder essa situação no front;
- exibir aviso operacional para o usuário.

## 13. Finalizar conferência do setor

O setor só pode finalizar a atividade quando não houver bens pendentes impeditivos.

Condições mínimas:

- inventário atual existe;
- atividade do setor existe ou pode ser criada;
- todos os bens elegíveis foram classificados como:
  - `LOCALIZADO`;
  - `NÃO LOCALIZADO`;
  - divergente tratado;
  - situação especial aceita pela regra;
- não há bem `A INVENTARIAR` pendente;
- usuário confirma a finalização.

Operações:

- atualizar `inv_atividades.situacao = Finalizado`;
- preencher `termino = now()`;
- preencher `qtde_inventariada`;
- impedir novas alterações pela API mobile para aquela atividade.

O Expo deve mostrar o botão de finalizar apenas quando a API indicar `pode_finalizar = true`.

## 14. Endpoints recomendados

Manter os endpoints atuais de bens para consulta simples, mas criar endpoints específicos para conferência.

Sugestão:

- `GET /mobile-api/conferencia/atual`
  - retorna inventário atual, atividade do setor, resumo e permissões.

- `GET /mobile-api/conferencia/bens`
  - lista bens esperados do setor com status da conferência.

- `POST /mobile-api/conferencia/validar-leitura`
  - recebe `codigo` e retorna o resultado da validação.

- `POST /mobile-api/conferencia/localizar`
  - recebe `bem_id` ou `codigo` e confirma localização.

- `POST /mobile-api/conferencia/nao-localizados`
  - recebe lista de bens e justificativa.

- `POST /mobile-api/conferencia/divergencias`
  - registra divergência/observação.

- `POST /mobile-api/conferencia/finalizar`
  - finaliza a atividade do setor quando permitido.

Todos os endpoints devem usar `auth:sanctum`.

## 15. Contrato de resumo da conferência

Resposta recomendada para `/conferencia/atual`:

```json
{
  "inventario": {
    "id": 14,
    "numero": 2,
    "ano": 2024,
    "situacao": "Em andamento"
  },
  "atividade": {
    "id": 123,
    "setor": 456,
    "situacao": "Aberto",
    "pode_editar": true,
    "pode_finalizar": false
  },
  "resumo": {
    "total": 100,
    "localizados": 60,
    "pendentes": 30,
    "nao_localizados": 5,
    "divergentes": 3,
    "outro_setor": 2
  }
}
```

## 16. Regras para o Expo

A tela `app/conferencia.tsx` deve evoluir para o fluxo principal.

Ela deve conter:

- cabeçalho com usuário, unidade e setor;
- resumo da conferência;
- botão de leitura por câmera;
- campo para digitação manual do patrimônio;
- lista de pendentes;
- lista de últimas leituras;
- filtros por status;
- detalhes do bem em modal ou tela;
- ações disponíveis conforme resposta da API.

O Expo deve:

- chamar a API para carregar a conferência atual;
- chamar a API para validar cada leitura;
- exibir feedback imediato;
- atualizar o estado local após confirmação bem-sucedida;
- sempre recarregar o resumo após ações que mudam status;
- tratar falha de rede sem perder clareza para o usuário;
- não gravar regra patrimonial crítica apenas no front;
- não permitir finalizar se a API retornar `pode_finalizar = false`;
- não aceitar setor/unidade digitados pelo usuário como fonte de verdade.

## 17. Estados visuais no Expo

Usar status claros:

- verde: localizado/confirmado;
- amarelo: pendente/a inventariar;
- vermelho: não localizado ou erro;
- azul: outro setor, informação ou transferência;
- cinza: finalizado/bloqueado.

Mensagens devem ser curtas:

- "Bem localizado no setor."
- "Bem já conferido neste inventário."
- "Bem pertence a outro setor."
- "Bem não encontrado no cadastro."
- "Inventário do setor finalizado."
- "Justificativa obrigatória."

## 18. Segurança e consistência

Regras obrigatórias no backend:

- usar transação para gravações de conferência;
- impedir duplicidade em `mat_itensinventario`;
- nunca confiar em `setor`, `unidade` ou `usuario` enviados pelo app;
- auditar com o usuário EGap vinculado;
- validar inventário e atividade a cada escrita;
- bloquear escrita em atividade finalizada;
- retornar HTTP adequado:
  - `200` para sucesso;
  - `201` para criação;
  - `400` para regra de negócio inválida;
  - `401` para não autenticado;
  - `403` para sem permissão ou atividade bloqueada;
  - `404` para bem não encontrado;
  - `409` para duplicidade ou conflito de estado;
  - `422` para validação.

## 19. Pontos fora da primeira entrega

Não implementar na primeira versão sem nova regra detalhada:

- transferência real de bens;
- geração de termo no mobile;
- assinatura eletrônica;
- edição definitiva de cadastro patrimonial;
- inclusão definitiva de bem novo;
- modo offline com sincronização;
- fechamento geral do inventário, além da atividade do setor.

Esses pontos existem no legado, mas têm impacto em termo, transferência, auditoria e responsabilidade administrativa.

## 20. Critério de aceite da próxima etapa

A próxima implementação deve ser considerada pronta quando:

- Laravel tiver endpoints específicos de conferência;
- Expo carregar a conferência atual;
- Expo listar bens esperados com status;
- scanner validar patrimônio no backend;
- confirmação de localização gravar no banco;
- não localizado exigir justificativa;
- duplicidade de leitura não criar duplicidade no banco;
- resumo atualizar após cada ação;
- atividade finalizada bloquear novas gravações;
- regras críticas estiverem centralizadas no Laravel.
