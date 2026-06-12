<?php

namespace App\Filament\Livewire\Patrimonio;

use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensMoveis\TransferenciaBemMovel;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class MateriaisTermoModal extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public int $termoId;

    public function mount(int $termoId): void
    {
        $this->termoId = $termoId;
    }

    public function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->query($this->getMateriaisQuery())
            ->columns([
                TableColumns::text('NumPatrimonio', 'Patrimônio', isFirstColumn: true)
                    ->badge()
                    ->color('primary'),

                TextColumn::make('bem.Descricao')
                    ->label('Descrição')
                    ->default('-')
                    ->weight('medium')
                    ->wrap(),

                TextColumn::make('origem')
                    ->label('Origem')
                    ->getStateUsing(fn (TransferenciaBemMovel $record): string => self::localizacao(
                        $record->unidadeAnteriorRel?->UnidadeOrganizacional,
                        $record->setorAnteriorRel?->Setor,
                        $record->complementoAnteriorRel?->descricao,
                        $record->UnidadeAnterior,
                        $record->SetorAnterior,
                    ))
                    ->wrap(),

                TextColumn::make('destino')
                    ->label('Destino')
                    ->getStateUsing(fn (TransferenciaBemMovel $record): string => self::localizacao(
                        $record->unidadeAtualRel?->UnidadeOrganizacional,
                        $record->setorAtualRel?->Setor,
                        $record->complementoAtualRel?->descricao,
                        $record->UnidadeAtual,
                        $record->SetorAtual,
                    ))
                    ->wrap(),

                TableColumns::dateTime('date_time', 'Movimentado em')
                    ->description(fn (TransferenciaBemMovel $record): string => $record->usuarioRef?->name ?? '-'),
            ])
            ->defaultSort('id')
            ->defaultPaginationPageOption(15)
            ->paginated([15])
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->emptyStateHeading('Nenhum material está vinculado a este termo')
            ->actions([])
            ->bulkActions([]);
    }

    private function getMateriaisQuery(): Builder
    {
        return TransferenciaBemMovel::query()
            ->where('Termo', $this->termoId)
            ->with([
                'bem',
                'unidadeAnteriorRel',
                'setorAnteriorRel',
                'complementoAnteriorRel',
                'unidadeAtualRel',
                'setorAtualRel',
                'complementoAtualRel',
                'usuarioRef',
            ]);
    }

    private static function localizacao(
        ?string $unidade,
        ?string $setor,
        ?string $complemento,
        mixed $unidadeId,
        mixed $setorId,
    ): string {
        $partes = array_filter([
            $unidade ?: ($unidadeId ? "Unidade {$unidadeId}" : null),
            $setor ?: ($setorId ? "Setor {$setorId}" : null),
            $complemento,
        ]);

        return $partes ? implode(' / ', $partes) : '-';
    }

    public function render(): View
    {
        return view('livewire.patrimonio.materiais-modal');
    }
}
