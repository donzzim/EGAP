# Backend EGAP

Aplicação Laravel 11 com painel Filament 3 e API autenticada por Laravel Sanctum para o aplicativo móvel.

A arquitetura integrada, os contratos da API e as regras de negócio estão descritos no [README principal](../README.md).

## Superfícies Principais

| Superfície | Caminho | Responsabilidade |
|---|---|---|
| Painel Filament | `/egap` | Gestão patrimonial, pedidos, almoxarifado, relatórios e administração |
| API mobile | `/mobile-api` | Autenticação, dashboard, bens, conferência e pedidos |
| Rotas da API | `routes/api.php` | Endpoints protegidos por `auth:sanctum` |
| Provider do painel | `app/Providers/Filament/EgapPanelProvider.php` | Configuração do painel e login `LoginEgap` |

## Execução Local

```powershell
composer install
npm install

# Configure o .env antes de executar migrations ou acessar o banco.
php artisan migrate
php artisan serve
```

Em outro terminal:

```powershell
npm run dev
```

O painel fica disponível em `http://127.0.0.1:8000/egap`.

## Banco E Autenticação

Os módulos patrimoniais e de pedidos possuem consultas e transações explícitas em `DB::connection('egap')`; a conexão nomeada `egap` precisa existir no ambiente e apontar para as tabelas legadas (`mat_*`, `ped_*`, `alm_*`, `jos_users`). Ela aceita variáveis `EGAP_DB_*` e reutiliza `DB_*` quando não forem definidas.

O model `App\Models\User` representa a tabela usada pela autenticação Laravel e pelos tokens Sanctum e, na configuração atual, usa a conexão `emes` para o banco `emes`. Essa conexão também precisa conter as tabelas criadas pelas migrations de autenticação e tokens.

## Verificação

```powershell
php artisan route:list --path=mobile-api
php artisan test
vendor\bin\pint --test
```
