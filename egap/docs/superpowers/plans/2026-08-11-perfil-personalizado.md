# Página de Perfil Personalizada Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Substituir a página de perfil padrão do Filament (`/egap/profile`) por uma versão que permite editar avatar/CPF/telefone/matrícula/número funcional e exibe, somente leitura, cargo/lotação/perfis de acesso/datas vindos do sistema legado (`jos_users`, `mat_infousers`, `mat_lotacao`).

**Architecture:** Uma classe `App\Filament\Pages\Auth\EditProfile` estende `Filament\Auth\Pages\EditProfile` e sobrescreve `form()` para acrescentar seções ao formulário padrão (nome/email/senha continuam vindo dos métodos herdados). A lógica de formatação dos dados institucionais fica isolada numa classe pura `App\Filament\Pages\Auth\ProfileInstitutionalDataResolver`, testável sem tocar o banco. O `EgapPanelProvider` passa a apontar `->profile()` para a nova classe.

**Tech Stack:** Laravel 12, Filament v4 (`filament/filament: ~4.0`), PHPUnit (sem Pest), Spatie Permission (`HasRoles`), MySQL (conexões `egap`/`emes` já configuradas nos models existentes).

## Global Constraints

- Todo texto de UI (labels, mensagens) em português, seguindo o padrão já usado em `UsersEgapResource` e demais Resources.
- Nenhum teste pode tocar o banco de dados real: `DB_CONNECTION=mysql` / `DB_DATABASE=egap` está configurado até para o ambiente de testes (`sqlite` está comentado em `phpunit.xml`). Os testes existentes (`CpfHelperTest`, `ArquivoDigitalTest`, `MobileAuthControllerTest`) usam `PHPUnit\Framework\TestCase` puro e nunca persistem registros — este plano segue o mesmo padrão.
- Reaproveitar relações e métodos já existentes (`UserEgap::currentAuthenticated()`, `UserEgap::infoUser()`, `UserEgap::lotacoes()`, `User::getFilamentAvatarUrl()`) — não duplicar essa lógica nem alterar os models.
- Máscaras de campo: CPF usa `999.999.999-99`; telefone usa `(**)*****-****` (mesma máscara já usada em `app/Filament/Resources/Agendamento/EquipeResource.php:69`).
- Upload de avatar: disco `public`, diretório `avatars`, visibilidade `public` (mesmo padrão de `DescricaoResumidaResource`/`ValidarTermoResource`).
- Rodar `vendor\bin\pint` nos arquivos tocados antes de cada commit (convenção do projeto, `CLAUDE.md`).

---

### Task 1: Resolver de dados institucionais (cargo, lotação, perfis, datas)

**Files:**
- Create: `app/Filament/Pages/Auth/ProfileInstitutionalDataResolver.php`
- Test: `tests/Unit/ProfileInstitutionalDataResolverTest.php`

**Interfaces:**
- Consumes: `App\Models\UserEgap` (props `block` bool, `registerDate` `?Carbon`, `lastvisitDate` `?Carbon`), `App\Models\Admin\InfoUser` (prop `cargo` `?string`), `App\Models\Admin\Lotacao` (relations `unidadeJudiciaria` e `setorRef`, ambas `App\Models\Cadastro\Setores` com prop `Setor` `?string`).
- Produces: `ProfileInstitutionalDataResolver::resolve(?UserEgap $userEgap, ?InfoUser $infoUser, ?Lotacao $lotacao, array $roleNames): array` retornando um array associativo com as chaves `cargo`, `unidade_judiciaria`, `setor`, `bloqueado_legado`, `perfis_acesso`, `data_cadastro`, `ultimo_acesso` — todos `string`, nunca `null` (usa `'-'` como placeholder). Task 2 consome exatamente este método e estas chaves.

- [ ] **Step 1: Escrever o teste que cobre a ausência total de vínculo legado**

Criar `tests/Unit/ProfileInstitutionalDataResolverTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Filament\Pages\Auth\ProfileInstitutionalDataResolver;
use App\Models\Admin\InfoUser;
use App\Models\Admin\Lotacao;
use App\Models\Cadastro\Setores;
use App\Models\UserEgap;
use PHPUnit\Framework\TestCase;

class ProfileInstitutionalDataResolverTest extends TestCase
{
    public function test_retorna_traco_para_todos_os_campos_quando_nao_ha_vinculo_legado(): void
    {
        $data = ProfileInstitutionalDataResolver::resolve(null, null, null, []);

        $this->assertSame('-', $data['cargo']);
        $this->assertSame('-', $data['unidade_judiciaria']);
        $this->assertSame('-', $data['setor']);
        $this->assertSame('-', $data['bloqueado_legado']);
        $this->assertSame('Nenhum perfil atribuído', $data['perfis_acesso']);
        $this->assertSame('-', $data['data_cadastro']);
        $this->assertSame('-', $data['ultimo_acesso']);
    }

    public function test_monta_cargo_lotacao_e_perfis_a_partir_dos_vinculos_legados(): void
    {
        $userEgap = new UserEgap([
            'block' => true,
            'registerDate' => '2020-03-10 08:00:00',
            'lastvisitDate' => '2026-08-10 14:30:00',
        ]);

        $infoUser = new InfoUser(['cargo' => 'ANALISTA JUDICIARIO']);

        $lotacao = new Lotacao;
        $lotacao->setRelation('unidadeJudiciaria', new Setores(['Setor' => 'TRIBUNAL DE JUSTICA']));
        $lotacao->setRelation('setorRef', new Setores(['Setor' => 'Seção de Patrimônio']));

        $data = ProfileInstitutionalDataResolver::resolve($userEgap, $infoUser, $lotacao, ['Administrador', 'Almoxarife']);

        $this->assertSame('ANALISTA JUDICIARIO', $data['cargo']);
        $this->assertSame('TRIBUNAL DE JUSTICA', $data['unidade_judiciaria']);
        $this->assertSame('Seção de Patrimônio', $data['setor']);
        $this->assertSame('Sim', $data['bloqueado_legado']);
        $this->assertSame('Administrador, Almoxarife', $data['perfis_acesso']);
        $this->assertSame('10/03/2020', $data['data_cadastro']);
        $this->assertSame('10/08/2026 14:30', $data['ultimo_acesso']);
    }
}
```

- [ ] **Step 2: Rodar o teste e confirmar que falha (classe ainda não existe)**

Run: `vendor\bin\phpunit tests/Unit/ProfileInstitutionalDataResolverTest.php`
Expected: FAIL com `Class "App\Filament\Pages\Auth\ProfileInstitutionalDataResolver" not found`.

- [ ] **Step 3: Implementar o resolver**

Criar `app/Filament/Pages/Auth/ProfileInstitutionalDataResolver.php`:

```php
<?php

namespace App\Filament\Pages\Auth;

use App\Models\Admin\InfoUser;
use App\Models\Admin\Lotacao;
use App\Models\UserEgap;

class ProfileInstitutionalDataResolver
{
    /**
     * @param  array<int, string>  $roleNames
     * @return array<string, string>
     */
    public static function resolve(?UserEgap $userEgap, ?InfoUser $infoUser, ?Lotacao $lotacao, array $roleNames): array
    {
        return [
            'cargo' => $infoUser?->cargo ?: '-',
            'unidade_judiciaria' => $lotacao?->unidadeJudiciaria?->Setor ?: '-',
            'setor' => $lotacao?->setorRef?->Setor ?: '-',
            'bloqueado_legado' => $userEgap === null ? '-' : ($userEgap->block ? 'Sim' : 'Não'),
            'perfis_acesso' => $roleNames === [] ? 'Nenhum perfil atribuído' : implode(', ', $roleNames),
            'data_cadastro' => $userEgap?->registerDate?->format('d/m/Y') ?? '-',
            'ultimo_acesso' => $userEgap?->lastvisitDate?->format('d/m/Y H:i') ?? '-',
        ];
    }
}
```

- [ ] **Step 4: Rodar o teste e confirmar que passa**

Run: `vendor\bin\phpunit tests/Unit/ProfileInstitutionalDataResolverTest.php`
Expected: PASS (2 testes, sem erros).

- [ ] **Step 5: Lint e commit**

```bash
vendor\bin\pint app/Filament/Pages/Auth/ProfileInstitutionalDataResolver.php tests/Unit/ProfileInstitutionalDataResolverTest.php
git add app/Filament/Pages/Auth/ProfileInstitutionalDataResolver.php tests/Unit/ProfileInstitutionalDataResolverTest.php
git commit -m "feat: adiciona resolver de dados institucionais do perfil"
```

---

### Task 2: Página de perfil personalizada (`App\Filament\Pages\Auth\EditProfile`)

**Files:**
- Create: `app/Filament/Pages/Auth/EditProfile.php`

**Interfaces:**
- Consumes: `Filament\Auth\Pages\EditProfile` (classe base — fornece `getNameFormComponent()`, `getEmailFormComponent()`, `getPasswordFormComponent()`, `getPasswordConfirmationFormComponent()`, `getCurrentPasswordFormComponent()`, `getUser(): Authenticatable&Model`); `App\Filament\Pages\Auth\ProfileInstitutionalDataResolver::resolve()` (Task 1); `App\Models\UserEgap::currentAuthenticated(): ?UserEgap`; `UserEgap::infoUser` (relação `HasOne`, já carregada via propriedade dinâmica); `UserEgap::lotacoes(): HasMany`.
- Produces: classe `App\Filament\Pages\Auth\EditProfile` pronta para ser passada a `Panel::profile()` na Task 3. Nenhuma outra classe deste plano depende dela.

Esta task não tem teste automatizado dedicado: é composição de schema do Filament (framework), e qualquer teste de integração exigiria autenticar um usuário Livewire contra o banco real de produção (`DB_DATABASE=egap`), o que viola a Global Constraint de não tocar o banco nos testes. A verificação é manual, na Task 3, após o registro da página no painel.

- [ ] **Step 1: Criar a classe da página**

Criar `app/Filament/Pages/Auth/EditProfile.php`:

```php
<?php

namespace App\Filament\Pages\Auth;

use App\Models\Admin\Lotacao;
use App\Models\UserEgap;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Conta')
                    ->schema([
                        FileUpload::make('avatar_url')
                            ->label('Foto')
                            ->avatar()
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public')
                            ->columnSpanFull(),
                        $this->getNameFormComponent(),
                        $this->getLoginFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        $this->getCurrentPasswordFormComponent(),
                    ])
                    ->columns(2),
                Section::make('Dados Pessoais')
                    ->schema([
                        TextInput::make('cpf')
                            ->label('CPF')
                            ->mask('999.999.999-99')
                            ->maxLength(14),
                        TextInput::make('telefone')
                            ->label('Telefone')
                            ->mask('(**)*****-****')
                            ->maxLength(15),
                        TextInput::make('matricula')
                            ->label('Matrícula')
                            ->maxLength(255),
                        TextInput::make('numero_funcional')
                            ->label('Número Funcional')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Informações Institucionais')
                    ->description('Dados sincronizados do sistema EGAP. Para corrigir, procure a Administração.')
                    ->schema($this->getInstitutionalFormComponents())
                    ->columns(2),
            ]);
    }

    protected function getLoginFormComponent(): Component
    {
        return TextInput::make('login')
            ->label('Login')
            ->disabled()
            ->dehydrated(false);
    }

    /**
     * @return array<int, Component>
     */
    protected function getInstitutionalFormComponents(): array
    {
        $data = $this->resolveInstitutionalData();

        return [
            Placeholder::make('ativo')
                ->label('Status da conta')
                ->content($this->getUser()->getAttribute('ativo') ? 'Ativo' : 'Inativo'),
            Placeholder::make('cargo')
                ->label('Cargo')
                ->content($data['cargo']),
            Placeholder::make('perfis_acesso')
                ->label('Perfis de acesso')
                ->content($data['perfis_acesso']),
            Placeholder::make('unidade_judiciaria')
                ->label('Unidade Judiciária')
                ->content($data['unidade_judiciaria']),
            Placeholder::make('setor')
                ->label('Setor')
                ->content($data['setor']),
            Placeholder::make('data_cadastro')
                ->label('Cadastrado em')
                ->content($data['data_cadastro']),
            Placeholder::make('ultimo_acesso')
                ->label('Último acesso')
                ->content($data['ultimo_acesso']),
            Placeholder::make('bloqueado_legado')
                ->label('Bloqueado no sistema legado')
                ->content($data['bloqueado_legado']),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function resolveInstitutionalData(): array
    {
        $userEgap = UserEgap::currentAuthenticated();

        $lotacao = $userEgap
            ?->lotacoes()
            ->with(['unidadeJudiciaria:id,Setor', 'setorRef:id,Setor'])
            ->first();

        return ProfileInstitutionalDataResolver::resolve(
            $userEgap,
            $userEgap?->infoUser,
            $lotacao instanceof Lotacao ? $lotacao : null,
            $this->getUser()->getRoleNames()->values()->all(),
        );
    }
}
```

- [ ] **Step 2: Lint**

Run: `vendor\bin\pint app/Filament/Pages/Auth/EditProfile.php`
Expected: sem alterações necessárias ou aplicadas automaticamente sem erro.

- [ ] **Step 3: Commit**

```bash
git add app/Filament/Pages/Auth/EditProfile.php
git commit -m "feat: adiciona pagina de perfil personalizada com dados institucionais"
```

---

### Task 3: Registrar a página no painel e verificar manualmente

**Files:**
- Modify: `app/Providers/Filament/EgapPanelProvider.php:39`

**Interfaces:**
- Consumes: `App\Filament\Pages\Auth\EditProfile` (Task 2), `Filament\Panel::profile(?string $page = EditProfile::class, bool $isSimple = true): static` (assinatura já confirmada em `vendor/filament/filament/src/Panel/Concerns/HasAuth.php:269`).
- Produces: painel `egap` servindo a nova página em `/egap/profile`. Nada mais depende disto.

- [ ] **Step 1: Trocar a página de perfil registrada**

Em `app/Providers/Filament/EgapPanelProvider.php`, trocar a linha 39:

```php
            ->profile(isSimple: false)
```

por:

```php
            ->profile(\App\Filament\Pages\Auth\EditProfile::class, isSimple: false)
```

- [ ] **Step 2: Limpar caches de configuração/rotas do Filament**

Run: `php artisan optimize:clear`
Expected: saída confirmando que caches de config, rotas, views e eventos foram limpos, sem erros.

- [ ] **Step 3: Confirmar que a rota de perfil aponta para a nova classe**

Run: `php artisan route:list --path=egap/profile`
Expected: uma linha `GET|HEAD egap/profile` com o controller/handler referenciando `App\Filament\Pages\Auth\EditProfile` (não mais `Filament\Auth\Pages\EditProfile`).

- [ ] **Step 4: Verificar manualmente no navegador**

Run: `php artisan serve` (ou o servidor de desenvolvimento já em uso pelo projeto)

Acessar `http://localhost:8000/egap` (ajustar host/porta conforme ambiente), logar com um usuário existente, ir em **/egap/profile** e conferir:
- A seção "Conta" mostra upload de foto, nome, login (desabilitado), email e os campos de senha.
- A seção "Dados Pessoais" mostra CPF com máscara `999.999.999-99` e telefone com máscara `(**)*****-****`, matrícula e número funcional editáveis.
- A seção "Informações Institucionais" mostra o status da conta (`Ativo`/`Inativo`, vindo de `users.ativo`) sempre, e cargo/lotação/perfis/datas/bloqueio legado quando o usuário logado tiver um `UserEgap` vinculado pelo `login`, mostrando `-`/"Nenhum perfil atribuído" quando não tiver (ex.: usuário `admin`, que não tem `infoUser`/`lotacao` vinculados, conforme observado durante o brainstorming).
- Salvar o formulário com uma alteração em CPF/telefone/matrícula e confirmar que persiste após recarregar a página.
- Fazer upload de uma foto e confirmar que o avatar aparece no menu superior do painel (usa `User::getFilamentAvatarUrl()`, já existente).

- [ ] **Step 5: Rodar a suíte de testes completa**

Run: `vendor\bin\phpunit`
Expected: todos os testes passam, incluindo os novos de `ProfileInstitutionalDataResolverTest`.

- [ ] **Step 6: Lint final e commit**

```bash
vendor\bin\pint app/Providers/Filament/EgapPanelProvider.php
git add app/Providers/Filament/EgapPanelProvider.php
git commit -m "feat: registra pagina de perfil personalizada no painel egap"
```
