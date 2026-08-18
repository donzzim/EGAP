<div>
    <div class="mb-4 flex justify-end print:hidden">
        <x-filament::button color="gray" icon="heroicon-o-printer" onclick="window.print()">
            Imprimir
        </x-filament::button>
    </div>

    <div class="pb-4">
        {{ $this->table }}
    </div>

    <div
        class="sticky bottom-0 z-10 -mx-4 border-t border-gray-200 bg-white/95 px-4 py-3 backdrop-blur print:hidden dark:border-white/10 dark:bg-gray-900/95 sm:-mx-6 sm:px-6"
    >
        <div class="flex flex-wrap items-center justify-center gap-2">
            <x-filament::button
                color="primary"
                icon="heroicon-o-arrows-right-left"
                wire:click="mountTableBulkAction('transferir_bens')"
                :disabled="empty($this->selectedTableRecords)"
            >
                Transferir Bens
            </x-filament::button>

            <x-filament::button
                color="info"
                icon="heroicon-o-plus-circle"
                wire:click="mountAction('incluirBem')"
            >
                Incluir Bem
            </x-filament::button>

            @if ($this->inventarioAtivoParaSetor())
                <x-filament::button
                    color="danger"
                    icon="heroicon-o-hand-thumb-down"
                    wire:click="mountTableBulkAction('bem_nao_localizado')"
                    :disabled="empty($this->selectedTableRecords)"
                >
                    Bem Não Localizado
                </x-filament::button>

                <x-filament::button
                    color="success"
                    icon="heroicon-o-hand-thumb-up"
                    wire:click="mountTableBulkAction('confirmar_localizacao')"
                    :disabled="empty($this->selectedTableRecords)"
                >
                    Confirmar a Localização
                </x-filament::button>
            @endif

            @if ($this->podeFinalizarInventario())
                <x-filament::button
                    color="primary"
                    icon="heroicon-o-check-circle"
                    wire:click="mountAction('finalizarInventario')"
                >
                    Finalizar Inventário
                </x-filament::button>
            @endif
        </div>
    </div>
</div>
