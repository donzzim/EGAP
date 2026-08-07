# Laravel 11 → 12 Upgrade (Fase A) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Upgrade the EGAP Laravel application from Laravel 11 to Laravel 12 with Filament kept at v3, with the test suite green, Pint clean, and `/mobile-api` + `/egap` panel unaffected.

**Architecture:** Run the official `laravel/upgrade` tool, then manually reconcile `bootstrap/app.php`, `config/*.php`, and middleware against the Laravel 12 upgrade guide. Filament v3 stays untouched — that migration is Fase B and is out of scope here.

**Tech Stack:** Laravel 11→12, PHP 8.2+ (repo currently runs PHP 8.5.4 CLI), Filament 3.3 (unchanged), PHPUnit, Laravel Pint.

## Global Constraints

- Source of truth: `.claude/MIGRATION.md` — this plan implements only Section 3 (Fase A).
- Do NOT start any Filament v4 work (Fase B) — Fase A must be 100% merged and verified first.
- Do NOT use Pest syntax in any new/modified test — project uses PHPUnit only.
- Do NOT modify anything related to `inventario-mobile/` (does not exist in this repo checkout, but if it appears, it is out of scope).
- Do NOT touch business rules with fixed IDs (e.g. setor de Patrimônio = `1239`) or `mat_patrimonio.sit_inventario` / `mat_patrimonio.id_inventario` — legacy compatibility fields.
- Repo layout matches `.claude/MIGRATION.md`: git toplevel is `D:\PROGRAMACAO\EGAP`, containing `egap/` (the Laravel app, this working directory `D:\PROGRAMACAO\EGAP\EGAP`) and `inventario-mobile/` as siblings. The shell in this session starts already inside `egap/`, so commands run without an explicit `cd egap` — but `git status`/`git diff` output shows paths prefixed `egap/...` relative to the toplevel, which is expected.
- Known baseline facts (captured before this plan started, 2026-08-06):
  - `php artisan test` → 6 passed, 0 failed, 6 deprecated (PHP 8.5 `PDO::MYSQL_ATTR_SSL_CA` deprecation notices — pre-existing, unrelated to this migration).
  - `vendor/bin/pint --test` → **fails on 227 files already**, before any migration change. This is pre-existing drift on `main`, not something this migration introduces. Task 5 decides how to handle it — do not silently bulk-reformat 227 unrelated files without confirming scope first.
  - `php artisan route:list --path=mobile-api` → 17 routes across `Api\BensController`, `Api\ConferenciaBensController`, `Api\MobileAuthController`, `Api\PedidosController`. This is the baseline to diff against after the upgrade.
  - Fixed already (pre-existing bug unrelated to migration, blocked `route:list` from even running): `app/Http/Controllers/PrintsControllers/PrintController.php` had a UTF-8 BOM before `<?php`, causing a fatal autoload error. BOM was stripped. This was a standalone fix, not part of the Fase A branch — see Task 1.

---

### Task 1: Baseline commit and branch creation

**Files:**
- Modify: `app/Http/Controllers/PrintsControllers/PrintController.php` (BOM fix — already applied in the working tree, needs to be committed on `main` before branching)

**Interfaces:**
- Produces: clean `main` branch with baseline fix committed; new branch `upgrade/laravel-12` ready for Task 2.

- [ ] **Step 1: Confirm the BOM fix is the only pending change**

Run: `git status --short`
Expected: only `app/Http/Controllers/PrintsControllers/PrintController.php` listed as modified.

- [ ] **Step 2: Commit the BOM fix to `main`**

```bash
git add app/Http/Controllers/PrintsControllers/PrintController.php
git commit -m "fix: remove UTF-8 BOM breaking autoload of PrintController"
```

This is a pre-existing bug fix, independent of the Laravel 12 migration — it must land on `main` directly, not inside the `upgrade/laravel-12` branch, per the "não misturar mudanças" rule in `.claude/MIGRATION.md` (a bug fix is not a Fase A change).

- [ ] **Step 3: Create the Fase A branch**

```bash
git checkout -b upgrade/laravel-12
```

- [ ] **Step 4: Re-confirm baseline on the new branch**

Run: `php artisan test && php artisan route:list --path=mobile-api`
Expected: 6 passed / 0 failed; 17 routes listed (same as pre-branch baseline above).

---

### Task 2: Manual dependency bump per the official Laravel 12 upgrade guide

> **Correction (2026-08-06):** `.claude/MIGRATION.md` instructs `composer require laravel/upgrade --dev` + `php artisan upgrade`. That package **does not exist on Packagist** (`composer show laravel/upgrade` → "not found"). Confirmed with the user — proceeding with the official manual guide at https://laravel.com/docs/12.x/upgrade instead, which lists Laravel 12 as a ~5-minute, low-risk upgrade with no `bootstrap/app.php` changes required (those were a Laravel 10→11 change, not 11→12).

**Files:**
- Modify: `composer.json`, `composer.lock` (via composer)

**Interfaces:**
- Consumes: clean `upgrade/laravel-12` branch from Task 1.
- Produces: Laravel core bumped to `^12.0`, `phpunit/phpunit` to `^11.0`, for Task 3 to review against the guide's other breaking-change list.

- [ ] **Step 1: Bump version constraints in `composer.json`**

Change `require.laravel/framework` from `^11.0` to `^12.0`, and `require-dev.phpunit/phpunit` from `^10.5` to `^11.0`. (Project does not use Pest, so `pestphp/pest` does not apply.)

- [ ] **Step 2: Run composer update scoped to the affected packages**

```bash
composer update laravel/framework phpunit/phpunit nunomaduro/collision --with-dependencies
```

Using `--with-dependencies` lets Composer resolve compatible versions of transitive deps (e.g. `nunomaduro/collision ^8.0` already supports both; `spatie/laravel-ignition`, `laravel/sanctum`, `laravel/sail`, `spatie/laravel-permission`, `mockery/mockery`, `fakerphp/faker` should not need forced bumps but may shift patch versions).

- [ ] **Step 3: Inspect what changed**

Run: `git status --short && git diff --stat`
Expected: `composer.json` + `composer.lock` changed only. No application source files should change from this step alone.

- [ ] **Step 4: Commit the dependency bump**

```bash
git add composer.json composer.lock
git commit -m "chore: bump laravel/framework to ^12.0 and phpunit/phpunit to ^11.0"
```

---

### Task 3: Manual review against the official Laravel 12 breaking-change list

**Files:**
- Read-only grep sweep across `app/`, `config/`, `routes/`
- Modify only the specific files a grep hit confirms are affected

**Interfaces:**
- Consumes: dependency bump from Task 2.
- Produces: confirmation that none of the six documented Laravel 12 breaking changes affect this app, or fixes applied where they do.

The official guide (https://laravel.com/docs/12.x/upgrade) lists these breaking changes for an 11.x app with no `bootstrap/app.php` impact. Check each:

- [ ] **Step 1: `HasUuids` trait now generates UUIDv7 instead of ordered UUIDv4**

```bash
grep -rn "HasUuids" app/
```
If any model uses it and the project relies on the old ordered-UUIDv4 format (check for UUID format assumptions in tests/DB columns), switch that model to `Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids`. If no hits, no action needed.

- [ ] **Step 2: `image` validation rule no longer allows SVG by default**

```bash
grep -rn "'image'" app/ --include=*.php
grep -rn "image:" app/ --include=*.php
```
If any validation rule uses bare `image` (form requests, Filament form rules) and the project needs to accept SVG uploads, add `allow_svg` (e.g. `'photo' => 'required|image:allow_svg'`). If no hits or SVG was never required, no action needed.

- [ ] **Step 3: Local disk default root path changed to `storage/app/private`**

Read `config/filesystems.php` — if a `local` disk is already explicitly configured with its own `root`, this change doesn't apply. If it relies on Laravel's default, confirm nothing in the app assumes the old `storage/app` default path for `Storage::disk('local')`.

- [ ] **Step 4: Duplicate named routes now resolve to the first match, not the last**

```bash
php artisan route:list --json 2>/dev/null | php -r '$r=json_decode(stream_get_contents(STDIN),true); $names=array_filter(array_column($r,"name")); $dupes=array_diff_assoc($names,array_unique($names)); print_r($dupes);'
```
If any duplicate route names print, confirm the app doesn't depend on the last-registered one winning (uncommon, but check `/mobile-api` and `/egap` routes specifically since those are the two entry points this migration must not break).

- [ ] **Step 5: `mergeIfMissing()` with dot-notation keys**

```bash
grep -rn "mergeIfMissing" app/
```
If no hits, no action needed.

- [ ] **Step 6: Custom `DatabaseTokenRepository` subclass**

```bash
grep -rn "DatabaseTokenRepository" app/
```
If no hits (this app uses Sanctum for mobile auth and the `pessoa` guard, not the password-reset broker), no action needed.

- [ ] **Step 7: Boot the app locally**

```bash
php artisan serve
```

Hit `/egap` in a browser (or `curl -I http://127.0.0.1:8000/egap`) and confirm it responds (redirect to login is expected, not a 500). Stop the server after checking.

- [ ] **Step 8: Commit any fixes from Steps 1-6**

```bash
git add -A
git commit -m "fix: address Laravel 12 breaking changes (see step notes)"
```

(Only run this if Steps 1-6 actually required a code change — if all six checks came back clean, skip the commit, nothing to record.)

---

### Task 4: Verify mobile API and panel controllers

**Files:**
- Read-only verification: `app/Http/Controllers/Api/MobileAuthController.php`, `app/Http/Controllers/Api/BensController.php`, `app/Http/Controllers/Api/ConferenciaBensController.php`, `app/Http/Controllers/Api/PedidosController.php`
- Modify only if the diff in Task 4 Step 1 finds a break: same files.

**Interfaces:**
- Consumes: baseline route list from Task 1, Step 4 (17 routes).
- Produces: confirmation that `/mobile-api/*` routes are unchanged in shape after the Laravel 12 bump.

- [ ] **Step 1: Re-run route:list and diff against baseline**

```bash
php artisan route:list --path=mobile-api > /tmp/routes-after.txt
```

Compare against the 17-route baseline captured in Task 1. Expected: identical route list (same URIs, same controller@method, same names).

- [ ] **Step 2: Run the targeted test for mobile auth**

```bash
php artisan test --filter=MobileAuthControllerTest
```

Expected: PASS (this test already passes at baseline — it must still pass).

- [ ] **Step 3: Confirm `EgapPanelProvider.php` still lints clean**

```bash
php -l app/Providers/Filament/EgapPanelProvider.php
```

Expected: `No syntax errors detected`.

---

### Task 5: Full verification and Pint scope decision

**Files:**
- Potentially: any file `vendor/bin/pint` would reformat, if the user opts to fix the pre-existing 227-file drift as part of this branch.

**Interfaces:**
- Consumes: all prior tasks complete.
- Produces: Fase A completion state per `.claude/MIGRATION.md` section 3's exit criteria.

- [ ] **Step 1: Run the full test suite**

```bash
php artisan test
```

Expected: all green, no new failures vs. the Task 1 baseline (6 passed). Investigate and fix any regression before proceeding — do not silence failing tests.

- [ ] **Step 2: Run Pint in check mode, scoped to files this branch touched**

```bash
git diff main --name-only -- '*.php' | xargs -r vendor/bin/pint --test
```

Result: `git diff main --name-only -- '*.php'` returned **no PHP files** — Fase A only changed `composer.json`/`composer.lock`. The pre-existing 227-file Pint drift on `main` is untouched by this branch. Asked the user (2026-08-06): treat it as separate, out-of-scope tech debt rather than bulk-reformatting 227 unrelated files inside `upgrade/laravel-12`. Decision: **leave it as follow-up debt**, not part of Fase A completion.

- [ ] **Step 3: `php artisan serve` smoke test**

```bash
php artisan serve
```

Confirm `/egap` panel loads (login screen, still Filament v3 UI) and `php artisan route:list --path=mobile-api` still shows 17 routes. Stop the server after checking.

- [ ] **Step 4: Final commit**

```bash
git add -A
git commit -m "chore: Laravel 12 upgrade verification (Fase A complete)"
```

(Only if Steps 1-2 produced file changes beyond what Tasks 2-4 already committed.)

- [ ] **Step 5: Report using the format from `.claude/MIGRATION.md` section 6**

For every non-mechanical fix made during this branch (i.e. everything beyond what `laravel/upgrade` did automatically), report:
- **Causa raiz**
- **Correção aplicada** (with exact file path)
- **Como validar** (exact command run)

---

## Exit Criteria (from `.claude/MIGRATION.md` section 3)

- [ ] `php artisan test` green
- [ ] `vendor/bin/pint --test` clean (scope confirmed with user per Task 5 Step 2)
- [ ] `php -l app/Providers/Filament/EgapPanelProvider.php` clean
- [ ] `php artisan serve` boots, `/egap` loads (Filament v3 still)
- [ ] Branch `upgrade/laravel-12` merged before any Fase B work starts
