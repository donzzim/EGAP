<?php

namespace App\Services;

use App\Filament\Livewire\Patrimonio\MateriaisBaixaModal;
use App\Filament\Livewire\Patrimonio\MateriaisTermoModal;
use App\Filament\Livewire\Patrimonio\ComissoesModal;
use App\Filament\Livewire\Patrimonio\UnidadesModal;
use App\Filament\Livewire\PortalTransparencia\AlmoxarifadoCharts;
use App\Filament\Livewire\PortalTransparencia\PatrimonioCharts;
use Livewire\Livewire;

class LivewireComponentsService
{
    public static function getLivewireComponents(): void
    {
        Livewire::component('patrimonio.materiais-baixa-modal', MateriaisBaixaModal::class);
        Livewire::component('patrimonio.materiais-termo-modal', MateriaisTermoModal::class);
        Livewire::component('patrimonio.inventario-comissoes-modal', ComissoesModal::class);
        Livewire::component('patrimonio.inventario-unidades-modal', UnidadesModal::class);
        Livewire::component('portal-transparencia.patrimonio-charts', PatrimonioCharts::class);
        Livewire::component('portal-transparencia.almoxarifado-charts', AlmoxarifadoCharts::class);
    }
}
