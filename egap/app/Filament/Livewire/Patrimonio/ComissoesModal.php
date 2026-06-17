<?php

namespace App\Filament\Livewire\Patrimonio;

use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensMoveis\InventarioComissao;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ComissoesModal extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public int $inventarioId;

    public function mount(int $inventarioId): void
    {
        $this->inventarioId = $inventarioId;
    }

    public function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->query($this->getComissoesQuery())
            ->columns([
                TableColumns::text('inventario_name', 'Inventário', isFirstColumn: true)
                    ->badge()
                    ->state(fn (InventarioComissao $record): string => $record->inventario
                        ? "{$record->inventario->num_inventario}/{$record->inventario->ano_inventario}"
                        : '-'),
                TableColumns::text('comissao', 'Comissão')
                    ->weight('medium'),

                TableColumns::text('funcao', 'Função')
                    ->badge()
                    ->color('gray'),

                TableColumns::text('membroRef.name', 'Membro')
                    ->weight('medium'),

                TableColumns::dateTime('date_time', 'Atualizado em'),
            ])
            ->defaultSort('id')
            ->defaultPaginationPageOption(15)
            ->paginated([15])
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateHeading('Nenhuma comissão vinculada a este inventário')
            ->actions([])
            ->bulkActions([]);
    }

    private function getComissoesQuery(): Builder
    {
        return InventarioComissao::query()
            ->where('id_inventario', $this->inventarioId)
            ->with(['inventario', 'membroRef']);
    }

    public function render(): View
    {
        return view('livewire.patrimonio.comissoes-modal');
    }
}
