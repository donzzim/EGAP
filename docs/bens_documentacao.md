# Documentação — `bens.php`

## Visão Geral

O arquivo `bens.php` é a **página principal de gestão de bens patrimoniais de um setor** dentro de um sistema web de controle de patrimônio institucional. Ele combina lógica de backend em PHP (consultas ao banco de dados, controle de sessão) com uma interface interativa em HTML, CSS e JavaScript (jQuery + Bootstrap).

---

## 1. Redirecionamento Inicial

Ao ser acessado diretamente pelo nome do arquivo (ex: `http://servidor/bens.php`), a página detecta essa situação e redireciona automaticamente o usuário para a rota padrão do sistema:

```
http://servidor/index.php?p=bens
```

Isso garante que a navegação siga o padrão de roteamento da aplicação.

---

## 2. Dados de Sessão Utilizados

A página depende das seguintes variáveis de sessão do usuário autenticado:

| Variável de Sessão     | Descrição                                      |
|------------------------|------------------------------------------------|
| `$_SESSION['idinventario']`    | ID do inventário em execução             |
| `$_SESSION['inventarioonline']`| Indica se há inventário ativo            |
| `$_SESSION['codigosetor']`     | Código do setor do usuário logado        |
| `$_SESSION['codigousuario']`   | Código do usuário logado                 |

Caso não haja inventário ativo na sessão, o sistema busca automaticamente o **último inventário finalizado** no banco de dados.

---

## 3. Consultas ao Banco de Dados

### 3.1 Consulta Principal de Bens

Busca todos os bens patrimoniais do setor com as seguintes situações:

| Código | Situação         |
|--------|------------------|
| 1      | Em uso (ativo)   |
| 7      | Em transferência |
| 8      | Cadastro manual  |

Os dados retornados incluem:

- Número de patrimônio e patrimônio anterior
- Descrição resumida e detalhada
- Marca e modelo
- Andar/localização no setor
- Estado de conservação
- Número de série
- Imagem ilustrativa
- Complemento do setor
- Acurácia
- Situação no inventário (`sit_inventario`)

### 3.2 Bens Pendentes de Inventário

Uma segunda consulta identifica os bens que **ainda não foram registrados** no inventário atual, excluindo itens já lançados em `mat_itensinventario` para o inventário e setor em questão.

### 3.3 Funções Auxiliares

| Função                    | Descrição                                              |
|---------------------------|--------------------------------------------------------|
| `marcas()`                | Retorna lista de marcas cadastradas                    |
| `modelos()`               | Retorna lista de modelos cadastrados                   |
| `descricoes()`            | Retorna descrições detalhadas dos bens                 |
| `verificaInventarioSetor()` | Verifica se o inventário do setor está aberto ou finalizado |

---

## 4. Lógica de Contagem de Bens a Inventariar

Para cada bem pendente, o sistema:

1. Consulta o histórico de movimentações/transferências (`mat_transferencia`);
2. Verifica a existência de termo digital válido (`mat_arquivodigital`);
3. Classifica o bem como **cadastro manual** se estiver na situação 8 sem termos válidos;
4. Incrementa o contador de bens a inventariar para os que estejam com situação `"A INVENTARIAR"` ou `"EM TRANSFERÊNCIA"` (exceto os que já estão na situação 7).

---

## 5. Interface do Usuário

### 5.1 Tabela de Bens

Exibe a relação de bens do setor em uma tabela com as colunas:

- **Patrimônio** (com código de barras)
- **Patrimônio Antigo**
- **Material** (descrição)
- **Complemento do Setor**
- **Situação do Inventário** *(visível apenas durante inventário ativo e não finalizado)*
- **Termo** (de responsabilidade)
- **Foto Ilustrativa**
- **Seleção** (checkbox individual e "Selecionar todos")
- **Observação** *(visível apenas na impressão)*

### 5.2 Filtros e Pesquisa

- **Campo de busca** por texto: pesquisa por número de patrimônio ou descrição do bem;
- **Filtro por complemento do setor**: permite segmentar a visualização por subdivisões do setor;
- **Botão Limpar**: redefine os filtros aplicados.

### 5.3 Exportação e Impressão

- **Imprimir**: imprime a relação de bens exibida na tela (com estilos otimizados para impressão via `@media print`);
- **Exportar**: gera um arquivo CSV/Excel com os bens listados, via `imprimir_bens.api.php?csv=on`.

---

## 6. Ações Disponíveis (Botões de Rodapé)

| Ação                      | Descrição                                                                 |
|---------------------------|---------------------------------------------------------------------------|
| **Transferir Bens**       | Abre confirmação para transferir bens selecionados para outro setor       |
| **Incluir Bem**           | Abre modal para solicitar inclusão de novo bem no sistema                 |
| **Bem Não Localizado**    | Registra que os bens selecionados não foram encontrados (exige justificativa) |
| **Confirmar a Localização** | Confirma a localização dos bens selecionados ou altera responsabilidade  |
| **Finalizar Inventário**  | Encerra o inventário do setor *(habilitado somente quando todos os bens foram inventariados)* |

---

## 7. Modais (Janelas Pop-up)

| Modal                        | Finalidade                                                                 |
|------------------------------|----------------------------------------------------------------------------|
| **Editar Patrimônio**        | Permite alterar dados de um bem específico (marca, modelo, descrição, etc.) |
| **Transferir Bens**          | Confirmação e seleção do setor destinatário para transferência             |
| **Termo de Responsabilidade** | Exibe e permite imprimir o termo gerado após transferência                |
| **Bem Não Localizado**       | Formulário para justificar a não localização dos bens selecionados         |
| **Solicitar Manutenção**     | Lista os bens selecionados e permite inserir justificativa por item        |
| **Novo Bem**                 | Formulário para cadastro/solicitação de inclusão de novo bem               |

---

## 8. Comunicação com APIs (AJAX)

A página se comunica com os seguintes endpoints via requisições AJAX (jQuery `$.ajax`):

| Endpoint                            | Método | Função                                              |
|-------------------------------------|--------|-----------------------------------------------------|
| `api/carregar_bens.api.php`         | POST   | Carrega e renderiza os bens na tabela               |
| `api/transferencia.api.php`         | POST   | Processa a transferência e o registro de não localizados |
| `api/termo_responsabilidade_html.api.php` | POST | Retorna o HTML do Termo de Responsabilidade   |
| `api/manutencao.api.php`            | POST   | Envia solicitação de manutenção dos bens            |
| `api/imprimir_bens.api.php`         | GET    | Gera relatório de bens para impressão ou exportação |

---

## 9. Dependências Front-end

- **Bootstrap** — layout responsivo, modais e componentes visuais
- **jQuery** — manipulação do DOM e requisições AJAX
- **Select2** — campos de seleção aprimorados (filtros e combos)
- **Font Awesome** — ícones dos botões
- **Toastr** (via `callToastr`) — notificações de sucesso e erro

---

## 10. Estilos e Impressão

A página conta com estilos específicos para impressão (`@media print`) que:

- Ocultam menus, cabeçalho, rodapé e botões de ação;
- Exibem campos de observação e patrimônio antigo (ocultos na tela);
- Aplicam bordas e zoom (`75%`) para melhor legibilidade no papel;
- Ocultam fotos ilustrativas para economizar tinta.

---

## Resumo Geral

> `bens.php` é a tela central de operação patrimonial de um setor. Permite que o responsável consulte, inventarie, transfira, edite e registre ocorrências sobre os bens sob sua responsabilidade, integrando-se a um sistema maior de controle de patrimônio institucional.
