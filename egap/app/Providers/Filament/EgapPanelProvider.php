<?php

namespace App\Providers\Filament;

use App\Filament\Auth\LoginApp;
use App\Filament\Auth\LoginResponse;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class EgapPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('egap')
            ->path('/egap')
            ->login(LoginApp::class)
            ->passwordReset()
            //->topNavigation()
            ->maxContentWidth(MaxWidth::Full)
            ->simplePageMaxContentWidth(MaxWidth::Small)
            ->sidebarCollapsibleOnDesktop() // funciona se tirar o topNavigation()
            ->emailVerification()
            ->profile(isSimple: false)
            ->globalSearch(false)
            ->brandName('EGAP')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->darkMode(true)
            ->maxContentWidth(MaxWidth::Full)
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Painel de Controle'),

                NavigationGroup::make()
                    ->label('Cadastro'),

                NavigationGroup::make()
                    ->label('Pedidos'),

                NavigationGroup::make()
                    ->label('Almoxarifado'),

                NavigationGroup::make()
                    ->label('Bens Imóveis'),

                NavigationGroup::make()
                    ->label('Bens Intangíveis'),

                NavigationGroup::make()
                    ->label('Bens Móveis'),

                NavigationGroup::make()
                    ->label('Agendamento'),

                NavigationGroup::make()
                    ->label('Pedidos'),

                NavigationGroup::make()
                    ->label('Administração'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('pessoa')
            ->spa();
    }

    public function boot(): void
    {
        $this->app->bind(LoginResponseContract::class, LoginResponse::class);
    }
}
