<?php

namespace App\Filament\Livewire\Patrimonio\BensMoveis;

use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableModalComponent;
use App\Models\Patrimonio\BensMoveis\InventarioEquipe;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EquipesModal extends TableModalComponent implements HasActions
{
    use InteractsWithActions;

    public int $unidadeId;

    public function mount(int $unidadeId, ?int $inventarioId = null): void
    {
        $this->unidadeId = $unidadeId;
    }

    public function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->query($this->getEquipesQuery())
            ->columns([
                TableColumns::text('integrantesRef.name', 'Integrante', isFirstColumn: true)
                    ->icon('heroicon-o-user')
                    ->weight('medium'),

                TableColumns::text('funcao', 'Função')
                    ->badge()
                    ->color(fn (?string $state): string => str($state)->ascii()->lower()->contains('lider') ? 'primary' : 'gray'),
            ])
            ->defaultSort('funcao')
            ->defaultPaginationPageOption(15)
            ->paginated([15])
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateHeading('Nenhum integrante vinculado a esta unidade inventariada')
            ->recordActions([])
            ->toolbarActions([]);
    }

    private function getEquipesQuery(): Builder
    {
        return InventarioEquipe::query()
            ->where('unidade', $this->unidadeId)
            ->with('integrantesRef');
    }
}
