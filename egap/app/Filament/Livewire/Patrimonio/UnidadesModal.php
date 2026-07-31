<?php

namespace App\Filament\Livewire\Patrimonio;

use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableModalComponent;
use App\Models\Patrimonio\BensMoveis\Inventario;
use App\Models\Patrimonio\BensMoveis\InventarioUnidade;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UnidadesModal extends TableModalComponent
{
    public int $inventarioId;

    public function mount(int $inventarioId): void
    {
        $this->inventarioId = $inventarioId;
    }

    public function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->query($this->getUnidadesQuery())
            ->columns([
                TableColumns::text('unidade_inventariada', 'Unidade/Setor', isFirstColumn: true)
                    ->state(fn (InventarioUnidade $record): string => $record->rotuloUnidadeInventariada())
                    ->icon('heroicon-o-building-office')
                    ->weight('medium')
                    ->wrap(),

                TableColumns::text('setores_count', 'Setores')
                    ->badge()
                    ->counts('setores')
                    ->weight('medium'),

                TableColumns::text('dias', 'Dias')->suffix(' dias'),

                TableColumns::text('situacao', 'Situação')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Inventario::rotuloSituacao($state))
                    ->color(fn (?string $state): string => Inventario::corSituacao($state)),

                TableColumns::dateTime('date_time', 'Atualizado em'),
            ])
            ->defaultSort('id')
            ->defaultPaginationPageOption(15)
            ->paginated([15])
            ->emptyStateIcon('heroicon-o-building-office')
            ->emptyStateHeading('Nenhuma unidade vinculada a este inventário')
            ->actions([])
            ->bulkActions([]);
    }

    private function getUnidadesQuery(): Builder
    {
        return InventarioUnidade::query()
            ->where('id_inventario', $this->inventarioId)
            ->with('unidade.pai');
    }
}
