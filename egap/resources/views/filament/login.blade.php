{{--<x-filament-panels::page.simple> @if (filament()->hasRegistration()) <x-slot name="subheading"> {{ __('filament-panels::pages/auth/login.actions.register.before') }} {{ $this->registerAction }} </x-slot> @endif {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }} <x-filament-panels::form id="form" wire:submit="authenticate"> {{ $this->form }} <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="$this->hasFullWidthFormActions()" /> </x-filament-panels::form> {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }} </x-filament-panels::page.simple>--}}

<x-filament-panels::page.simple>
    <style>
        .fi-simple-layout {
            background: transparent !important;
        }

        .fi-simple-main-ctn {
            width: 100vw !important;
            min-height: 100vh !important;
            display: block !important;
            padding: 0 !important;
        }

        .fi-simple-main {
            width: 100vw !important;
            max-width: none !important;
            min-height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            border: none !important;
            outline: none !important;
            ring: none !important;
        }

        .fi-simple-header {
            display: none !important;
        }

        .fi-logo {
            display: none !important;
        }

        .egap-login-page {
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            position: relative;
            overflow: hidden;
        }

        .egap-login-page::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 64px 64px;
            opacity: 0.45;
        }

        .egap-login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1100px;
            min-height: 640px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            overflow: hidden;
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(18px);
        }

        .egap-login-brand {
            position: relative;
            padding: 48px;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background:
                linear-gradient(145deg, rgba(37, 99, 235, 0.95), rgba(2, 132, 199, 0.82)),
                linear-gradient(135deg, #1d4ed8, #0f172a);
            overflow: hidden;
        }

        .egap-login-brand::after {
            content: "";
            position: absolute;
            width: 360px;
            height: 360px;
            border-radius: 999px;
            right: -120px;
            bottom: -120px;
            background: rgba(255, 255, 255, 0.12);
        }

        .egap-brand-top,
        .egap-brand-content,
        .egap-brand-cards {
            position: relative;
            z-index: 1;
        }

        .egap-brand-top {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .egap-brand-logo {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.20);
            font-weight: 800;
            font-size: 18px;
        }

        .egap-brand-title {
            font-size: 22px;
            font-weight: 800;
            line-height: 1.1;
        }

        .egap-brand-subtitle {
            margin-top: 4px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.72);
        }

        .egap-badge {
            display: inline-flex;
            width: fit-content;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .egap-brand-content h1 {
            margin: 26px 0 0;
            max-width: 480px;
            font-size: 42px;
            line-height: 1.08;
            font-weight: 850;
            letter-spacing: -0.045em;
        }

        .egap-brand-content p {
            margin-top: 20px;
            max-width: 460px;
            font-size: 16px;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.76);
        }

        .egap-brand-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }

        .egap-brand-card {
            padding: 18px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(12px);
        }

        .egap-brand-card strong {
            display: block;
            font-size: 14px;
            margin-bottom: 6px;
        }

        .egap-brand-card span {
            display: block;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.68);
        }

        .egap-login-form-area {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px;
            background: #ffffff;
        }

        .egap-login-form-inner {
            width: 100%;
            max-width: 390px;
        }

        .egap-mobile-logo {
            display: none;
            width: 64px;
            height: 64px;
            margin: 0 auto 24px;
            border-radius: 22px;
            align-items: center;
            justify-content: center;
            background: #2563eb;
            color: #ffffff;
            font-weight: 800;
            font-size: 20px;
            box-shadow: 0 14px 30px rgba(37, 99, 235, 0.28);
        }

        .egap-form-header {
            margin-bottom: 28px;
        }

        .egap-form-kicker {
            margin: 0 0 10px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #2563eb;
        }

        .egap-form-header h2 {
            margin: 0;
            font-size: 32px;
            line-height: 1.15;
            font-weight: 850;
            letter-spacing: -0.045em;
            color: #0f172a;
        }

        .egap-form-header p {
            margin: 12px 0 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.65;
        }

        .egap-form-card {
            padding: 28px;
            border-radius: 24px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 22px 55px rgba(15, 23, 42, 0.10);
        }

        .egap-footer {
            margin-top: 24px;
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
        }

        .egap-login-form-area .fi-btn {
            min-height: 46px;
            border-radius: 14px;
            font-weight: 700;
        }

        .egap-login-form-area .fi-input-wrp {
            border-radius: 14px;
        }

        .egap-login-form-area .fi-input {
            min-height: 44px;
        }

        @media (max-width: 1024px) {
            .egap-login-wrapper {
                max-width: 520px;
                min-height: auto;
                grid-template-columns: 1fr;
            }

            .egap-login-brand {
                display: none;
            }

            .egap-login-form-area {
                padding: 36px 24px;
            }

            .egap-mobile-logo {
                display: flex;
            }

            .egap-form-header {
                text-align: center;
            }
        }

        @media (max-width: 520px) {
            .egap-login-page {
                padding: 18px 12px;
            }

            .egap-login-form-area {
                padding: 28px 18px;
            }

            .egap-form-card {
                padding: 22px;
            }

            .egap-form-header h2 {
                font-size: 28px;
            }
        }
    </style>

    <div class="egap-login-page">
        <div class="egap-login-wrapper">

            <section class="egap-login-brand">
                <div class="egap-brand-top">
                    <div class="egap-brand-logo">
                        E
                    </div>

                    <div>
                        <div class="egap-brand-title">
                            EGAP
                        </div>

                        <div class="egap-brand-subtitle">
                            Sistema Administrativo
                        </div>
                    </div>
                </div>

                <div class="egap-brand-content">
                    <span class="egap-badge">
                        Ambiente Administrativo
                    </span>

                    <h1>
                        Gestão patrimonial moderna, segura e integrada.
                    </h1>

                    <p>
                        Acesse o painel administrativo para gerenciar patrimônio,
                        almoxarifado, processos internos e demais recursos operacionais
                        de forma centralizada.
                    </p>
                </div>

                <div class="egap-brand-cards">
                    <div class="egap-brand-card">
                        <strong>Seguro</strong>
                        <span>Acesso controlado</span>
                    </div>

                    <div class="egap-brand-card">
                        <strong>Integrado</strong>
                        <span>Dados centralizados</span>
                    </div>

                    <div class="egap-brand-card">
                        <strong>Eficiente</strong>
                        <span>Fluxos otimizados</span>
                    </div>
                </div>
            </section>

            <section class="egap-login-form-area">
                <div class="egap-login-form-inner">

                    <div class="egap-mobile-logo">
                        E
                    </div>

                    <div class="egap-form-header">
                        <p class="egap-form-kicker">
                            Bem-vindo
                        </p>

                        <h2>
                            Acesse sua conta
                        </h2>

                        <p>
                            Informe seu login, CPF ou e-mail e sua senha para entrar no painel.
                        </p>
                    </div>

                    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

                    <div class="egap-form-card">
                        <x-filament-panels::form id="form" wire:submit="authenticate">
                            {{ $this->form }}

                            <x-filament-panels::form.actions
                                :actions="$this->getCachedFormActions()"
                                :full-width="$this->hasFullWidthFormActions()"
                            />
                        </x-filament-panels::form>
                    </div>

                    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}

                    <p class="egap-footer">
                        © {{ now()->year }} EGAP. Acesso restrito a usuários autorizados.
                    </p>
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page.simple>
