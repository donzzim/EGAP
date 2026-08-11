# Página de Perfil Personalizada — Design

## Contexto

O painel `egap` (Filament v4) já expõe uma página de perfil padrão em
`/egap/profile`, habilitada via `->profile(isSimple: false)` no
`EgapPanelProvider`. Essa página padrão (`Filament\Auth\Pages\EditProfile`)
só oferece nome, email e senha.

A tabela `users` (guard `pessoa`, provider `users`) já possui colunas não
usadas por nenhuma tela hoje: `avatar_url`, `cpf`, `telefone`, `matricula`,
`numero_funcional`, `ativo`, `moodle_id`. Além disso, o usuário logado tem um
registro correspondente em `jos_users` (via `UserEgap::currentAuthenticated()`,
casado por `login`/`username`), que por sua vez pode ter dados
complementares em `mat_infousers` (cargo, casado por CPF normalizado) e
`mat_lotacao` (unidade judiciária/setor atual, casado por `usuario_id` do
`InfoUser`).

Objetivo: substituir a página de perfil padrão por uma versão rica que
permita editar os dados pessoais do usuário e exiba, em modo somente
leitura, as informações institucionais vindas do sistema legado.

## Escopo

- Substituir a página registrada em `->profile()` do `EgapPanelProvider`
  (mesma URL `/egap/profile`, mesmo link já existente no menu do usuário).
- Não alterar `User`, `UserEgap`, `InfoUser`, `Lotacao` — apenas reaproveitar
  o que já existe (`UserEgap::currentAuthenticated()`).
- Não criar telas novas de administração de perfil de outros usuários — é
  estritamente a tela "meu perfil" do usuário autenticado.

## Estrutura da página

Nova classe `App\Filament\Pages\Auth\EditProfile`, estendendo
`Filament\Auth\Pages\EditProfile`, sobrescrevendo `form()` para adicionar
seções ao formulário padrão (nome/email/senha continuam vindo dos métodos
`getNameFormComponent()`, `getEmailFormComponent()`, etc. da classe base).

### Seção "Conta" (editável — tabela `users`)

- Avatar: `FileUpload` (imagem, disco `public`, diretório `avatars`),
  persistido em `avatar_url`. Reaproveita `getFilamentAvatarUrl()` já
  implementado em `User`.
- Nome, Email — componentes padrão herdados da classe base.
- Login — `TextInput` desabilitado (`disabled()->dehydrated(false)`), exibido
  como referência pois é a chave de ligação com `jos_users`.
- Senha / confirmação / senha atual — comportamento padrão herdado.

### Seção "Dados Pessoais" (editável — tabela `users`)

- CPF — `TextInput` com `->mask('999.999.999-99')`.
- Telefone — `TextInput` com `->mask('(**)*****-****')` (mesma máscara usada
  em `EquipeResource`).
- Matrícula — `TextInput` texto livre.
- Número Funcional — `TextInput` texto livre.

### Seção "Informações Institucionais" (somente leitura, agregada)

Dados resolvidos uma vez em `mount()`/método privado
`resolveInstitutionalData()`, usando `UserEgap::currentAuthenticated()` →
`InfoUser` (via CPF normalizado, mesma lógica de `UserMobile`) →
`Lotacao` (mais recente, via `usuario_id` do `InfoUser`):

- Cargo (`InfoUser->cargo`)
- Lotação atual: Unidade Judiciária (`Lotacao->unidadeJudiciaria->Setor`) e
  Setor (`Lotacao->setorRef->Setor`)
- Status: `users.ativo` (Ativo/Inativo) e, se houver vínculo,
  `jos_users.block` (Bloqueado no legado: Sim/Não)
- Perfis de acesso: badges com `getRoleNames()` do usuário autenticado;
  texto "Nenhum perfil atribuído" quando vazio
- Datas: `jos_users.registerDate` (cadastro) e `jos_users.lastvisitDate`
  (último acesso), quando houver vínculo

Todos os campos desta seção usam `Placeholder`/`TextEntry`-like display
(componentes de formulário desabilitados, sem persistência) e mostram `-`
quando o dado não existe (sem vínculo `UserEgap`, sem `InfoUser` ou sem
`Lotacao` — casos legítimos, não erros).

## Registro no painel

Em `EgapPanelProvider::panel()`:

```php
->profile(\App\Filament\Pages\Auth\EditProfile::class, isSimple: false)
```

## Fora de escopo

- Validação de formato real de CPF (dígito verificador) — segue o padrão já
  usado no `UsersEgapResource`, que também não valida.
- Edição de cargo/lotação nesta tela (já existe fluxo próprio em
  `UsersEgapResource`, edição continua lá).
- Populações do sistema de roles (Spatie) — hoje não há nenhum role
  cadastrado; a seção só passa a mostrar algo quando roles existirem.
