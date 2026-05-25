<?php

namespace App\Providers;

use App\Filament\Livewire\PortalTransparencia\AlmoxarifadoCharts;
use App\Filament\Livewire\PortalTransparencia\PatrimonioCharts;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::component('portal-transparencia.patrimonio-charts', PatrimonioCharts::class);
        Livewire::component('portal-transparencia.almoxarifado-charts', AlmoxarifadoCharts::class);
    }
}
