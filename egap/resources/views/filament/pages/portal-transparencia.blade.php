<x-filament-panels::page>
    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem;">
        @livewire(\App\Filament\Livewire\PortalTransparencia\PatrimonioCharts::class)
        @livewire(\App\Filament\Livewire\PortalTransparencia\AlmoxarifadoCharts::class)
    </div>
</x-filament-panels::page>
