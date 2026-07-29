<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-start">
        <div class="lg:col-span-4">
            @livewire(
                \App\Filament\Livewire\Externo\Almoxarifado\CarrinhoPedidoForm::class,
                [],
                key('carrinho-pedido-form-' . $this->tipoMaterial())
            )
        </div>

        <div class="lg:col-span-8">
            @livewire(
                \App\Filament\Livewire\Externo\Almoxarifado\MateriaisDisponiveisTable::class,
                ['tipoMaterial' => $this->tipoMaterial()],
                key('materiais-disponiveis-table-' . $this->tipoMaterial())
            )
        </div>
    </div>
</x-filament-panels::page>
