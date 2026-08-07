# Guia de Importação e Configuração — EGAP

> Passo a passo para colocar o projeto rodando em uma máquina nova (clone/pull recente), após a migração para **Laravel 12 + Filament v4**.

## 1. Pré-requisitos

- **PHP 8.2+** com as extensões `pdo_mysql`, `mbstring`, `openssl`, `curl`, `zip`, `gd` (padrão em qualquer instalação PHP moderna).
- **Composer** (gerencia as dependências PHP).
- **MySQL/MariaDB** rodando e acessível — o painel `/egap` usa `SESSION_DRIVER=database` e `CACHE_STORE=database`, então **sem banco disponível a aplicação não sobe corretamente** (inclusive a própria tela de login retorna erro sem conexão).
- **Node.js + npm** — opcional. Só é necessário se for mexer nos assets próprios da aplicação (`resources/css/app.css`, `resources/js/app.js`, via Vite). O Filament publica os próprios assets (CSS/JS do painel) direto em `public/`, sem depender do Vite.

## 2. Clonar/atualizar o repositório

```bash
git clone https://github.com/donzzim/EGAP.git
cd EGAP/egap
```

Se já tiver o repositório clonado, apenas:

```bash
git checkout main
git pull origin main
```

## 3. Instalar as dependências PHP

```bash
composer install
```

**Não use `composer update`** — isso ignoraria o `composer.lock` e poderia trazer versões diferentes das testadas no projeto (especialmente `laravel/framework` e `filament/filament`, que tiveram upgrade recente para v12/v4).

O `composer install` já dispara automaticamente (via hook `post-autoload-dump` no `composer.json`):
- `php artisan package:discover`
- `php artisan filament:upgrade` (republica os assets públicos do Filament em `public/css` e `public/js`)

Não é necessário rodar esses comandos manualmente.

## 4. Configurar o `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Edite o `.env` e configure a conexão com o banco. O projeto usa **duas conexões** apontando (normalmente) para o mesmo banco físico, mas com variáveis de ambiente separadas:

- **`mysql`** (conexão padrão do Laravel — sessões, cache, filas, autenticação Sanctum): usa `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
- **`egap`** (tabelas legadas `mat_*`, `ped_*`, `alm_*`, `jos_users` — todo o domínio de negócio do painel): usa `EGAP_DB_HOST`, `EGAP_DB_PORT`, `EGAP_DB_DATABASE`, `EGAP_DB_USERNAME`, `EGAP_DB_PASSWORD` — **e cai automaticamente para as variáveis `DB_*` se as `EGAP_DB_*` não existirem** (`config/database.php`). Ou seja, se for o mesmo banco/credenciais, basta preencher só `DB_*`.

Exemplo mínimo no `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=egap
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Se o banco `egap` estiver em host/credenciais diferentes do banco padrão do Laravel, preencha também as variáveis `EGAP_DB_*` correspondentes.

## 5. Limpar caches

Sempre que trocar de branch ou puxar mudanças grandes (como esta migração), limpe todos os caches para evitar referências antigas (classes/rotas/views compiladas):

```bash
php artisan optimize:clear
```

## 6. Migrations

```bash
php artisan migrate
```

O banco de dados principal (`mat_*`, `ped_*`, `alm_*`, `jos_users`) é legado e já deve existir previamente — as migrations do Laravel cobrem apenas as tabelas nativas do framework (`sessions`, `cache`, `jobs`, `personal_access_tokens`, etc.). Se essas tabelas nativas já existirem no seu banco, o comando não faz nada além de confirmar que está tudo em dia.

## 7. (Opcional) Assets do frontend próprio

Só necessário se for alterar `resources/css/app.css` ou `resources/js/app.js`:

```bash
npm install
npm run build   # build de produção
# ou
npm run dev     # modo desenvolvimento com hot reload
```

## 8. Subir a aplicação

```bash
php artisan serve
```

Acesse:
- **Painel administrativo**: `http://localhost:8000/egap` (redireciona para `/egap/login` se não autenticado)
- **API mobile**: `http://localhost:8000/mobile-api/*` (protegida por Sanctum — confira `php artisan route:list --path=mobile-api` para a lista completa de rotas)

## 9. Comandos úteis de verificação

```bash
php artisan test                          # roda a suíte de testes
vendor/bin/pint --test                    # confere formatação (Laravel Pint)
php artisan route:list --path=egap        # lista as rotas do painel
php artisan route:list --path=mobile-api  # lista as rotas da API mobile
```

## Observações importantes

- O app **`inventario-mobile/`** (Expo/React Native, consome `/mobile-api`) é um projeto separado, fora do escopo deste setup — tem seu próprio processo de instalação (`npm install` dentro da própria pasta).
- Este projeto está atualmente em **Laravel 12 + Filament v4** (migração concluída — ver `docs/superpowers/plans/` para o histórico detalhado das duas fases da migração).
- Guarda de autenticação do painel: `pessoa` (customizado, não é o guard `web` padrão do Laravel).
