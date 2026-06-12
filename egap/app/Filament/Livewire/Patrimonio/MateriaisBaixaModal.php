<?php

namespace App\Filament\Livewire\Patrimonio;

use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensMoveis\ItemBaixa;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class MateriaisBaixaModal extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public int $baixaId;

    public function mount(int $baixaId): void
    {
        $this->baixaId = $baixaId;
    }

    public function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->query($this->getMateriaisQuery())
            ->columns([
                TableColumns::text('bem.NumPatrimonio', 'Patrimônio', isFirstColumn: true)
                    ->badge()
                    ->color('primary'),

                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->getStateUsing(fn (ItemBaixa $record): string => $record->bem?->descricaoDetalhadaRef?->descricao_detalhada
                        ?: $record->bem?->descricao_detalhada
                        ?: '-')
                    ->weight('medium')
                    ->wrap(),

                TableColumns::text('bem.situacaoBemRef.descricao_completa', 'Situação atual')
                    ->badge()
                    ->color('gray'),

                TableColumns::text('situacaoDestino.descricao_completa', 'Situação de destino')
                    ->badge()
                    ->color(fn (ItemBaixa $record): string => (int) $record->id_situacao === 3 ? 'danger' : 'warning'),
            ])
            ->defaultSort('id')
            ->defaultPaginationPageOption(15)
            ->paginated([15])
            ->emptyStateIcon('heroicon-o-archive-box')
            ->emptyStateHeading('Nenhum material está vinculado a esta baixa')
            ->actions([])
            ->bulkActions([]);
    }

    private function getMateriaisQuery(): Builder
    {
        return ItemBaixa::query()
            ->where('id_baixa', $this->baixaId)
            ->with([
                'bem.descricaoDetalhadaRef',
                'bem.situacaoBemRef',
                'situacaoDestino',
            ]);
    }

    public function render(): View
    {
        return view('livewire.patrimonio.materiais-modal');
    }
}
