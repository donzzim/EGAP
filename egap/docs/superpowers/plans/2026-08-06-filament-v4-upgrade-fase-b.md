# Filament v3 → v4 Upgrade (Fase B) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Upgrade the EGAP Filament panel from v3.3 to v4.x with `php artisan test` green, Pint clean on touched files, the `/egap` panel booting with the `pessoa` guard + custom login working, and `/mobile-api/*` (which has no Filament dependency) untouched.

**Architecture:** Run the official `filament/upgrade` rector-based script first — it handles the bulk of mechanical namespace renames (Forms/Infolists → Schemas, icon strings → `Heroicon` enum where it can infer them, method renames). Manual work is reserved for what the script can't infer: custom auth pages, widget signatures, and anything the script flags as unresolved. Work module-by-module in the priority order `.claude/MIGRATION.md` section 4.2 point 5 defines, verifying after each module.

**Tech Stack:** Filament 3.3 → `^4.0`, Laravel 12 (Fase A already merged to `main`), PHP 8.2+, PHPUnit, Laravel Pint.

## Global Constraints

- Source of truth: `.claude/MIGRATION.md` — this plan implements Section 4 (Fase B). Fase A is merged to `main` (confirmed 2026-08-06); this branch (`upgrade/filament-v4`) was created from that updated `main`, matching the golden rule in section 2.
- Do NOT use Pest syntax in any new/modified test — PHPUnit only.
- Do NOT touch `inventario-mobile/` or anything under `/mobile-api` — that stack has zero Filament dependency and must not regress.
- Do NOT rewrite a Resource from scratch "to simplify" — migrate syntax only, preserve behavior.
- Do NOT touch business rules with fixed IDs (e.g. setor de Patrimônio = `1239`) or `mat_patrimonio.sit_inventario` / `mat_patrimonio.id_inventario` without confirming with the user.
- Do NOT mix Fase A and Fase B changes in the same commit (moot now — Fase A is already merged, this branch only contains Fase B work).
- **Verified facts (2026-08-06), correcting/confirming `.claude/MIGRATION.md`'s claims:**
  - `filament/upgrade` **does exist** on Packagist (unlike `laravel/upgrade` in Fase A, which didn't) — dry-run resolved to `v4.12.6`, pulling in `rector/rector` and `phpstan/phpstan`. Install: `composer require filament/upgrade:"^4.0" -W --dev`. Run: `vendor/bin/filament-v4`.
  - Filament v4 introduces `Filament\Support\Icons\Heroicon` (enum), e.g. `->icon(Heroicon::OutlinedStar)`. Outlined variants are prefixed `Outlined`, solid variants are unprefixed or prefixed differently — confirm exact member names per icon by checking the enum itself (`vendor/filament/support/src/Icons/Heroicon.php`) once installed, don't guess names.
  - `Filament\Forms\Get` / `Filament\Forms\Set` (used in Table column/action closures per MIGRATION.md point 4.2.3) move to `Filament\Schemas\Components\Utilities\Get` / `Filament\Schemas\Components\Utilities\Set`.
  - Custom login pages move from extending `Filament\Pages\Auth\Login` to `Filament\Auth\Pages\Login`. Current file: `app/Filament/Auth/LoginEgap.php:19` — `use Filament\Pages\Auth\Login as BaseLogin;`.
  - This project's actual real-world date is 2026-08-06 — Filament's docs site currently flags 4.x as an old version with 5.x current. **Out of scope**: MIGRATION.md explicitly scopes this migration to v4, not v5. Do not jump to v5 without the user asking.
  - `composer.json` currently pins `"filament/filament": "3.3"` (exact version, no caret) — must become `^4.0`.
  - MIGRATION.md's module priority list (section 4.2 point 5) omits the `app/Filament/Resources/Cadastro/**` folder (11 resources: CentroCusto, ComplementoSetor, ContaContabil, DescricaoDetalhada, DescricaoResumida, ElementoDespesa, Fornecedores, Marcas, Modelos, Setores, SituacaoBem, UnidadesDeMedida). Treated here as its own task, placed after Administração (lowest risk — standalone lookup-table CRUD, no cross-module flow dependencies).
  - Known widget files powering the dashboard (`app/Filament/Pages/EgapDashboard.php`) — confirmed to exist, matching MIGRATION.md: `PatrimonioOverviewStats`, `PatrimonioMoveisPorSituacaoChart`, `PatrimonioMoveisPorAnoChart`, `PatrimonioImoveisPorContaChart`, `PatrimonioTopMateriaisValorTable`, all under `app/Filament/Widgets/EgapDashboard/`. There is also a second widget tree under `app/Filament/Widgets/PortalTransparencia/**` (Almoxarifado + Patrimonio subfolders) not mentioned in MIGRATION.md — covered under Task 12 (Relatórios/Portal Transparência).
  - No local MySQL is available in this session (confirmed during Fase A) — full browser-based smoke testing of `/egap` is not possible here. Verification in this plan relies on `php -l`, `php artisan test`, and `php artisan route:list`/`config:cache` (which force Filament to boot-register every resource/page/widget, so a fatal error anywhere surfaces immediately even without a working DB connection for session storage). You must do a real click-through validation once MySQL is available.

---

### Task 1: Run the official Filament v4 upgrade script

**Files:**
- Modify: `composer.json`, `composer.lock`
- Modify: whatever `vendor/bin/filament-v4` rewrites (unknown until run)

**Interfaces:**
- Consumes: clean `upgrade/filament-v4` branch (forked from `main` post-Fase-A).
- Produces: mechanical renames applied repo-wide, for Task 2+ to triage what's left broken.

- [ ] **Step 1: Install the upgrade tool**

```bash
composer require filament/upgrade:"^4.0" -W --dev
```

- [ ] **Step 2: Run the rector-based upgrade script**

```bash
vendor/bin/filament-v4
```

Follow any interactive prompts (it may ask about directory structure changes — per Global Constraints, do NOT run `filament:upgrade-directory-structure-to-v4` unless the user explicitly asks; keep the existing folder layout to minimize unrelated diff noise).

- [ ] **Step 3: Point composer.json at Filament v4 and update**

```bash
composer require filament/filament:"^4.0" -W --no-update
composer update
```

- [ ] **Step 4: Remove the upgrade tool (one-time use)**

```bash
composer remove filament/upgrade --dev
```

- [ ] **Step 5: Inspect the full diff**

Run: `git status --short && git diff --stat`
Read `git diff` in full — this is the input for every subsequent task's triage. Do not skim; note every file the script touched vs. left alone.

- [ ] **Step 6: Commit the raw script output as-is**

```bash
git add -A
git commit -m "chore: run filament/upgrade script for Filament v4"
```

---

### Task 2: Get the app booting again (fatal-error triage)

**Files:**
- Unknown until Task 1's diff is reviewed — this task fixes whatever prevents `php artisan` commands from running at all.

**Interfaces:**
- Consumes: Task 1's diff.
- Produces: a codebase where `php artisan test`, `php artisan route:list`, and `php artisan config:cache` at least run without a fatal PHP error (individual test failures are fine at this stage — total silence/fatal crash is not).

- [ ] **Step 1: Attempt to boot**

```bash
php artisan about 2>&1 | head -40
```

If this fatals, read the error, fix the root cause (missing class, wrong namespace the script didn't catch, etc.), and repeat until it succeeds. This is the systematic-debugging loop — form a hypothesis from the exact error message and file:line, fix only that, re-run.

- [ ] **Step 2: Run the test suite**

```bash
php artisan test 2>&1 | tail -60
```

Record pass/fail count. Do not chase every failure yet — Task 2 only clears fatal/boot-level breakage (autoload errors, missing classes). Per-resource logic failures get fixed in that resource's module task (Tasks 5-13).

- [ ] **Step 3: Commit boot fixes**

```bash
git add -A
git commit -m "fix: resolve fatal errors after Filament v4 script run"
```

(Skip if Step 1 succeeded with no changes needed.)

---

### Task 3: Panel provider and custom auth (`pessoa` guard, `LoginEgap`, `LoginResponse`)

**Files:**
- Modify: `app/Providers/Filament/EgapPanelProvider.php`
- Modify: `app/Filament/Auth/LoginEgap.php`
- Modify: `app/Filament/Auth/LoginResponse.php`

**Interfaces:**
- Consumes: verified fact from Global Constraints — login pages extend `Filament\Auth\Pages\Login` in v4, not `Filament\Pages\Auth\Login`.
- Produces: login flow that still authenticates against the `pessoa` guard and preserves the CPF/username/email login-type detection in `getCredentialsFromFormData()`.

- [ ] **Step 1: Check what the Task 1 script already changed here**

```bash
git show HEAD -- app/Providers/Filament/EgapPanelProvider.php app/Filament/Auth/LoginEgap.php app/Filament/Auth/LoginResponse.php
```

- [ ] **Step 2: Fix `LoginEgap.php` if the base class wasn't updated**

If `app/Filament/Auth/LoginEgap.php:19` still reads `use Filament\Pages\Auth\Login as BaseLogin;`, change it to `use Filament\Auth\Pages\Login as BaseLogin;`. Check every other `Filament\Forms\*` and `Filament\Pages\*` import in this file against what actually exists in `vendor/filament/` post-upgrade (`ls vendor/filament/filament/src/Auth/Pages/` to confirm the class is there) — fix each to match, preserving the CPF/login-type logic in `getCredentialsFromFormData()` untouched (business logic, not migration surface).

- [ ] **Step 3: Fix `LoginResponse.php` if the contract namespace moved**

```bash
grep -rn "LoginResponse" vendor/filament/filament/src/Auth/ 2>/dev/null
```

If `Filament\Http\Responses\Auth\Contracts\LoginResponse` no longer exists at that path, find its new location from the grep output and update both `app/Filament/Auth/LoginResponse.php` and the `$this->app->bind(...)` call in `EgapPanelProvider.php:104`.

- [ ] **Step 4: Verify the panel provider boots**

```bash
php -l app/Providers/Filament/EgapPanelProvider.php
php -l app/Filament/Auth/LoginEgap.php
php -l app/Filament/Auth/LoginResponse.php
php artisan route:list --path=egap 2>&1 | head -20
```

Expected: no syntax errors, and `/egap` routes list without a fatal error (this exercises panel boot, which registers the login page).

- [ ] **Step 5: Commit**

```bash
git add app/Providers/Filament/EgapPanelProvider.php app/Filament/Auth/LoginEgap.php app/Filament/Auth/LoginResponse.php
git commit -m "fix: migrate EgapPanelProvider and custom login to Filament v4 auth API"
```

---

### Task 4: Dashboard and widgets

**Files:**
- Modify (as needed): `app/Filament/Pages/EgapDashboard.php`
- Modify (as needed): `app/Filament/Widgets/EgapDashboard/PatrimonioOverviewStats.php`, `PatrimonioMoveisPorSituacaoChart.php`, `PatrimonioMoveisPorAnoChart.php`, `PatrimonioImoveisPorContaChart.php`, `PatrimonioTopMateriaisValorTable.php`, `AccountWidget.php`

**Interfaces:**
- Consumes: Task 1's mechanical renames.
- Produces: dashboard registers and its 5 custom widgets (per `EgapDashboard::getWidgets()`) instantiate without error.

- [ ] **Step 1: Lint every file in scope**

```bash
php -l app/Filament/Pages/EgapDashboard.php
for f in app/Filament/Widgets/EgapDashboard/*.php; do php -l "$f"; done
```

- [ ] **Step 2: Diff each against what the script changed**

```bash
git show HEAD -- app/Filament/Pages/EgapDashboard.php app/Filament/Widgets/EgapDashboard/
```

For each widget, confirm the base class it extends (`ChartWidget`, `StatsOverviewWidget` / `Widgets\StatsOverviewWidget`, or a table widget base) still exists at that namespace post-upgrade (`grep -rln "class ChartWidget" vendor/filament/ ; grep -rln "class StatsOverviewWidget" vendor/filament/`), and that any overridden method signatures (e.g. `getStats(): array`, `getData(): array`, `getType(): string`) still match the v4 base class signature — read the actual base class file in `vendor/filament/widgets/src/` to confirm, don't assume v3 signatures still apply.

- [ ] **Step 3: Fix `->icon()` calls on this page/widgets if still using bare strings**

`EgapDashboard.php` has two: `protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';` and `->icon('heroicon-m-x-mark')` in `getHeaderActions()`. Confirm whether v4's `$navigationIcon` property type still accepts a string or now expects `Heroicon` (check `Filament\Pages\Page` or `Filament\Panel\Concerns\HasNavigation` in vendor). Convert to `Heroicon::OutlinedChartBarSquare` / `Heroicon::MicroXMark` (verify exact enum member names in `vendor/filament/support/src/Icons/Heroicon.php` first) only if the string form is actually removed — if strings are still accepted, this is optional polish, not required.

- [ ] **Step 4: Route-list boot check**

```bash
php artisan route:list --path=egap 2>&1 | grep -i dashboard
```

- [ ] **Step 5: Commit**

```bash
git add app/Filament/Pages/EgapDashboard.php app/Filament/Widgets/EgapDashboard/
git commit -m "fix: migrate EgapDashboard and its widgets to Filament v4"
```

(Skip if nothing needed changing beyond what Task 1 already committed.)

---

### Task 5: Sweep remaining `Forms\Get`/`Forms\Set` inside Table closures

**Files:**
- Modify as needed among the 24 files identified pre-migration: `app/Filament/Resources/Patrimonio/BensMoveis/{TransferenciaBemResource,BemMovelResource,InventarioUnidadeResource,InventarioResource,AtividadeInventarioResource}.php`, `app/Filament/Resources/Cadastro/SetoresResource.php`, `app/Filament/Resources/Agendamento/TransporteResource.php`, `app/Filament/Resources/Agendamento/AgendamentoResource.php`, `app/Filament/Resources/Almoxarifado/{MovimentacaoEstoqueResource,PedidosResource,NotaFiscalResource}.php`, `app/Filament/Resources/Admin/{LotacaoResource,UsersEgapResource}.php`, `app/Filament/Resources/Pedidos/ValidarPedidoResource.php`, `app/Filament/Livewire/Externo/Patrimonio/{BensNoSetorTable,MateriaisPermanentesTable}.php`, `app/Filament/Livewire/Externo/{PedidosTable,Carrinho}.php`, `app/Filament/Clusters/ExternoCluster/Concerns/SelecionaSetorAtual.php`, `app/Filament/Pages/Patrimonio/BensMoveis/IncorporarBensPage.php`, `app/Filament/Pages/Relatorios/RelatoriosGerais.php`, `app/Filament/Clusters/PedidosCluster/Requisicao/SolicitarMateriais.php`, `app/Filament/Clusters/PedidosCluster/Requisicao/RelatorioPedidos.php`, `app/Filament/Clusters/PedidosCluster/AgendamentoEntregaRecolhimento.php`

**Interfaces:**
- Consumes: Task 1's mechanical renames (the script may have already fixed some/all of these).
- Produces: no remaining references to the removed `Filament\Forms\Get`/`Filament\Forms\Set` classes.

- [ ] **Step 1: Re-grep after the script ran**

```bash
grep -rln "Filament\\\\Forms\\\\Get\|Filament\\\\Forms\\\\Set\|use Filament\\\\Forms\\\\Get\|use Filament\\\\Forms\\\\Set" app/
```

- [ ] **Step 2: For each remaining hit, swap the import**

Change `use Filament\Forms\Get;` → `use Filament\Schemas\Components\Utilities\Get;` and `use Filament\Forms\Set;` → `use Filament\Schemas\Components\Utilities\Set;`. Leave every other line untouched — this is a pure import-path fix, not a logic change.

- [ ] **Step 3: Lint every touched file**

```bash
git diff --name-only | grep '\.php$' | xargs -r -n1 php -l
```

- [ ] **Step 4: Commit**

```bash
git add -A
git commit -m "fix: move Forms\\Get/Forms\\Set imports to Schemas\\Components\\Utilities namespace"
```

(Skip if Step 1 found nothing — means the script already handled all of them.)

---

### Task 6: Sweep remaining string-based Heroicons

**Files:**
- Modify as needed among the 110 files identified pre-migration (see `grep -rln "heroicon-" app --include=*.php` for the full list).

**Interfaces:**
- Consumes: Task 1's mechanical renames (the script's rector rules typically convert simple `->icon('heroicon-o-x')` calls automatically).
- Produces: no remaining bare `heroicon-*` strings, OR a confirmed decision that strings are still valid (see Global Constraints — verify before mass-editing).

- [ ] **Step 1: Re-grep after the script ran**

```bash
grep -rln "heroicon-" app --include=*.php | wc -l
```

- [ ] **Step 2: Decide whether string icons still work in v4**

```bash
grep -rn "function icon\|IconName\|string.*Heroicon" vendor/filament/support/src/Concerns/HasIcon.php 2>/dev/null
```

If the property/parameter type still accepts `string | Heroicon | null` (or similar union), string icons remain valid and this task is **optional cosmetic cleanup** — stop here unless the user wants full enum conversion. If the type was narrowed to `Heroicon`-only (breaking), every remaining hit must convert.

- [ ] **Step 3: If conversion is required, convert file by file**

For each remaining hit, read the exact icon name used, look up the corresponding `Heroicon` enum case in `vendor/filament/support/src/Icons/Heroicon.php` (outlined variants prefixed `Outlined`, e.g. `heroicon-o-user` → `Heroicon::OutlinedUser`; solid variants use a different prefix — check the actual enum file, don't guess), and replace the string literal with the enum case plus a `use Filament\Support\Icons\Heroicon;` import.

- [ ] **Step 4: Lint every touched file and commit**

```bash
git diff --name-only | grep '\.php$' | xargs -r -n1 php -l
git add -A
git commit -m "fix: convert remaining string icons to Heroicon enum"
```

(Skip entirely if Step 2 confirms strings are still supported and the user doesn't want full conversion — report the finding instead of guessing.)

---

### Task 7: Module — Pedidos (fluxo Pedidos → Patrimônio)

**Files:** `app/Filament/Resources/Pedidos/ValidarPedidoResource.php` and its `Pages/`, `app/Filament/Clusters/PedidosCluster.php` and everything under `app/Filament/Clusters/PedidosCluster/**`, `app/Filament/Resources/Almoxarifado/PedidosResource.php` and its `Pages/`, `app/Filament/Livewire/AtendimentoPedidos/**`, `app/Filament/Livewire/Externo/{PedidosTable,PedidoItensModal,Carrinho}.php`

**Interfaces:**
- Consumes: Tasks 1, 5, 6 (mechanical renames + import sweeps already applied).
- Produces: this module's files lint clean and don't break panel boot. Highest business-risk module per MIGRATION.md — flag anything uncertain rather than guessing.

- [ ] **Step 1: Lint every file in the module**

```bash
find app/Filament/Resources/Pedidos app/Filament/Clusters/PedidosCluster.php app/Filament/Clusters/PedidosCluster app/Filament/Resources/Almoxarifado/PedidosResource.php app/Filament/Livewire/AtendimentoPedidos app/Filament/Livewire/Externo/PedidosTable.php app/Filament/Livewire/Externo/PedidoItensModal.php app/Filament/Livewire/Externo/Carrinho.php -name '*.php' 2>/dev/null | xargs -n1 php -l
```

- [ ] **Step 2: Read `git diff` for each file the script touched in this module**

Confirm Task 1's mechanical changes preserved every `->action()`/`->visible()`/`->hidden()` closure's business logic — these are exactly the closures that use `Get`/`Set` and are highest-risk for the automated script to mis-transform. Do not skim; read the full before/after for each closure.

- [ ] **Step 3: Boot check**

```bash
php artisan route:list --path=egap 2>&1 | grep -i pedido
```

- [ ] **Step 4: Run any directly-relevant existing test**

```bash
php artisan test --filter=Pedido 2>&1
```

(There may be none — this codebase's test suite is currently thin on Filament-resource coverage. If no tests exist, note that in the final report rather than fabricating one, unless the user asks for new tests to be written.)

- [ ] **Step 5: Commit any fixes**

```bash
git add -A
git commit -m "fix: Filament v4 fixes for Pedidos module"
```

---

### Task 8: Module — Patrimônio (Bens Móveis, Imóveis, Intangíveis)

**Files:** everything under `app/Filament/Resources/Patrimonio/**`, `app/Filament/Clusters/PatrimonioCluster.php`, `app/Filament/Clusters/ExternoCluster/Patrimonio/**`, `app/Filament/Livewire/Patrimonio/**`, `app/Filament/Livewire/Externo/Patrimonio/**`, `app/Filament/Pages/Patrimonio/BensMoveis/**`

**Interfaces:**
- Consumes: Tasks 1, 5, 6.
- Produces: clean lint, no boot regressions. **Do not** touch `mat_patrimonio.sit_inventario` / `mat_patrimonio.id_inventario` field usage or the hardcoded setor `1239` reference if found — those are legacy compatibility, not migration surface (per Global Constraints).

- [ ] **Step 1: Lint every file in the module**

```bash
find app/Filament/Resources/Patrimonio app/Filament/Clusters/PatrimonioCluster.php app/Filament/Clusters/ExternoCluster/Patrimonio app/Filament/Livewire/Patrimonio app/Filament/Livewire/Externo/Patrimonio app/Filament/Pages/Patrimonio -name '*.php' 2>/dev/null | xargs -n1 php -l
```

- [ ] **Step 2: Read `git diff` for each file the script touched**

Same rationale as Task 7 Step 2 — verify closures kept their logic.

- [ ] **Step 3: Boot check**

```bash
php artisan route:list --path=egap 2>&1 | grep -iE "bem-movel|bem-imovel|bem-intangivel|patrimonio"
```

- [ ] **Step 4: Run any directly-relevant existing test**

```bash
php artisan test --filter=IncorporarBens 2>&1
```

(`Tests\Unit\IncorporarBensServiceTest` exists and covers Patrimônio incorporation logic — this one must stay green.)

- [ ] **Step 5: Commit any fixes**

```bash
git add -A
git commit -m "fix: Filament v4 fixes for Patrimonio module"
```

---

### Task 9: Module — Almoxarifado

**Files:** everything under `app/Filament/Resources/Almoxarifado/**` (except `PedidosResource.php`, already covered in Task 7), `app/Filament/Clusters/AlmoxarifadoCluster.php`, `app/Filament/Clusters/ExternoCluster/Almoxarifado/**`, `app/Filament/Livewire/Externo/Almoxarifado/**`

- [ ] **Step 1: Lint every file in the module**

```bash
find app/Filament/Resources/Almoxarifado app/Filament/Clusters/AlmoxarifadoCluster.php app/Filament/Clusters/ExternoCluster/Almoxarifado app/Filament/Livewire/Externo/Almoxarifado -name '*.php' 2>/dev/null | xargs -n1 php -l
```

- [ ] **Step 2: Read `git diff` for each file the script touched, verify closures**

- [ ] **Step 3: Boot check**

```bash
php artisan route:list --path=egap 2>&1 | grep -i almoxarifado
```

- [ ] **Step 4: Commit any fixes**

```bash
git add -A
git commit -m "fix: Filament v4 fixes for Almoxarifado module"
```

---

### Task 10: Module — Agendamento

**Files:** everything under `app/Filament/Resources/Agendamento/**`

- [ ] **Step 1: Lint every file in the module**

```bash
find app/Filament/Resources/Agendamento -name '*.php' | xargs -n1 php -l
```

- [ ] **Step 2: Read `git diff` for each file the script touched, verify closures** (this module has `AgendamentoResource.php` and `TransporteResource.php` flagged in the pre-migration `Forms\Get`/`Set` grep — double-check Task 5 actually covered both).

- [ ] **Step 3: Boot check**

```bash
php artisan route:list --path=egap 2>&1 | grep -iE "agendamento|equipe|frota|regiao|transporte"
```

- [ ] **Step 4: Commit any fixes**

```bash
git add -A
git commit -m "fix: Filament v4 fixes for Agendamento module"
```

---

### Task 11: Module — Processo

**Files:** everything under `app/Filament/Resources/Processo/**`

- [ ] **Step 1: Lint every file in the module**

```bash
find app/Filament/Resources/Processo -name '*.php' | xargs -n1 php -l
```

- [ ] **Step 2: Read `git diff` for each file the script touched, verify closures**

- [ ] **Step 3: Boot check**

```bash
php artisan route:list --path=egap 2>&1 | grep -iE "processo|materiai|documento"
```

- [ ] **Step 4: Commit any fixes**

```bash
git add -A
git commit -m "fix: Filament v4 fixes for Processo module"
```

---

### Task 12: Module — Relatórios Gerais / Portal Transparência

**Files:** `app/Filament/Pages/Relatorios/RelatoriosGerais.php`, `app/Filament/Pages/PortalTransparencia.php`, everything under `app/Filament/Widgets/PortalTransparencia/**`

- [ ] **Step 1: Lint every file in the module**

```bash
find app/Filament/Pages/Relatorios app/Filament/Pages/PortalTransparencia.php app/Filament/Widgets/PortalTransparencia -name '*.php' 2>/dev/null | xargs -n1 php -l
```

- [ ] **Step 2: Read `git diff` for each file the script touched, verify closures and chart widget base-class signatures** (same check as Task 4 Step 2 — `BaseChart.php` here is a shared base class other widgets extend, verify it first since a break there cascades).

- [ ] **Step 3: Boot check**

```bash
php artisan route:list --path=egap 2>&1 | grep -iE "relatorio|transparencia"
```

- [ ] **Step 4: Commit any fixes**

```bash
git add -A
git commit -m "fix: Filament v4 fixes for Relatorios/PortalTransparencia module"
```

---

### Task 13: Module — Administração (Usuários, Lotações, Permissões)

**Files:** `app/Filament/Resources/Admin/**`, `app/Filament/Clusters/AdminEgapCluster.php`, `app/Filament/Clusters/AdminEgapCluster/**`

- [ ] **Step 1: Lint every file in the module**

```bash
find app/Filament/Resources/Admin app/Filament/Clusters/AdminEgapCluster.php app/Filament/Clusters/AdminEgapCluster -name '*.php' 2>/dev/null | xargs -n1 php -l
```

- [ ] **Step 2: Read `git diff` for each file the script touched, verify closures** (note: `spatie/laravel-permission` powers this module — confirm the composer update in Task 1 Step 3 didn't need to bump it; if it did, re-check permission-related resource code against the installed version's API, don't assume it's unchanged).

- [ ] **Step 3: Boot check**

```bash
php artisan route:list --path=egap 2>&1 | grep -iE "usuario|lotacao|permissa|acesso"
```

- [ ] **Step 4: Commit any fixes**

```bash
git add -A
git commit -m "fix: Filament v4 fixes for Administracao module"
```

---

### Task 14: Module — Cadastro (not in MIGRATION.md's list, added per Global Constraints)

**Files:** everything under `app/Filament/Resources/Cadastro/**`

- [ ] **Step 1: Lint every file in the module**

```bash
find app/Filament/Resources/Cadastro -name '*.php' | xargs -n1 php -l
```

- [ ] **Step 2: Read `git diff` for each file the script touched, verify closures** (`SetoresResource.php` was flagged in the pre-migration `Forms\Get`/`Set` grep — confirm Task 5 covered it).

- [ ] **Step 3: Boot check**

```bash
php artisan route:list --path=egap 2>&1 | grep -iE "centro-custo|complemento-setor|conta-contabil|descricao|elemento-despesa|fornecedor|marca|modelo|setor|situacao-bem|unidade-medida"
```

- [ ] **Step 4: Commit any fixes**

```bash
git add -A
git commit -m "fix: Filament v4 fixes for Cadastro module"
```

---

### Task 15: Full verification and report

**Interfaces:**
- Consumes: all prior tasks complete.
- Produces: Fase B completion state per `.claude/MIGRATION.md` section 7's overall migration exit criteria (the parts achievable without a live DB/browser in this session).

- [ ] **Step 1: Full test suite**

```bash
php artisan test 2>&1 | tail -40
```

Expected: all green, no regressions vs. the Fase A baseline (6 passed). Investigate and fix any regression — do not silence failing tests.

- [ ] **Step 2: Full route registration boot check**

```bash
php artisan route:list 2>&1 | tail -5
php artisan route:list --path=mobile-api 2>&1
```

Confirm the 17 `/mobile-api` routes are byte-for-byte the same as the Fase A baseline (this module has zero Filament dependency — any diff here means something leaked across module boundaries, investigate immediately).

- [ ] **Step 3: Pint, scoped to files this branch touched**

```bash
git diff main --name-only -- '*.php' | xargs -r vendor/bin/pint --test
```

Same scope rule as Fase A: fix Pint issues in files this branch actually modified; leave the pre-existing (Fase-A-documented) drift on unrelated files as tracked debt unless the user says otherwise.

- [ ] **Step 4: `php -l` across the whole `app/Filament` tree as a final sweep**

```bash
find app/Filament -name '*.php' | xargs -n1 php -l 2>&1 | grep -v "No syntax errors"
```

Expected: empty output.

- [ ] **Step 5: Report using the format from `.claude/MIGRATION.md` section 6**

For every non-mechanical fix made across Tasks 2-14, report:
- **Causa raiz**
- **Correção aplicada** (with exact file path)
- **Como validar** (exact command run)

- [ ] **Step 6: Flag what still needs the user's manual validation**

Per `.claude/MIGRATION.md` section 7, these require a live environment this session doesn't have:
- Painel `/egap` loads and login with guard `pessoa` works (needs MySQL running).
- Fluxo Pedidos → Patrimônio validated end-to-end manually.
- `/mobile-api/*` responding via a real call (Postman/Insomnia or the mobile app) — the route list matching is a proxy signal, not a substitute for a real request/response check.
