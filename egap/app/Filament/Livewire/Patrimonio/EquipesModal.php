<?php

namespace App\Filament\Livewire\Patrimonio;

use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensMoveis\InventarioEquipe;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class EquipesModal extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

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
            ->actions([])
            ->bulkActions([]);
    }

    private function getEquipesQuery(): Builder
    {
        return InventarioEquipe::query()
            ->where('unidade', $this->unidadeId)
            ->with('integrantesRef');
    }

    public function render(): View
    {
        return view('livewire.patrimonio.equipes-modal');
    }
}
