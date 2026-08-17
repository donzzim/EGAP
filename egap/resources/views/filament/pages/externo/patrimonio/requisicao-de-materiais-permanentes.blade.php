<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-start">
        <div class="lg:col-span-4">
            @livewire(\App\Filament\Clusters\ExternoCluster\Livewire\Patrimonio\CarrinhoMateriaisPermanentesForm::class)
        </div>

        <div class="lg:col-span-8">
            @livewire(\App\Filament\Clusters\ExternoCluster\Livewire\Patrimonio\MateriaisPermanentesTable::class)
        </div>
    </div>
</x-filament-panels::page>
