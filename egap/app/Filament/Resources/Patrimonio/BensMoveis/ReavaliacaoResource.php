<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\ReavaliacaoResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use App\Models\Patrimonio\BensMoveis\Reavaliacao;
use Filament\Forms;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReavaliacaoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Reavaliacao::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationGroup = 'Bens Móveis';

    protected static ?string $navigationLabel = 'Reavaliação';

    protected static ?string $modelLabel = 'Reavaliação';

    protected static ?string $pluralModelLabel = 'Reavaliações';

    protected static ?int $navigationSort = 11;

    protected static ?string $slug = 'bens-moveis/reavaliacoes';

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->modifyQueryUsing(fn ($query) => $query->with(['bem', 'responsavel']))
            ->emptyStateIcon('heroicon-o-funnel')
            ->emptyStateHeading(fn ($livewire): string => filled(data_get($livewire, 'tableFilters.patrimonioFilter.patrimonio'))
                ? 'Nenhuma reavaliação encontrada'
                : 'Selecione um patrimônio no filtro')
            ->emptyStateDescription(fn ($livewire): string => filled(data_get($livewire, 'tableFilters.patrimonioFilter.patrimonio'))
                ? 'Não há registros de reavaliação para o patrimônio selecionado.'
                : 'A tabela de reavaliações só carrega registros após a busca por um patrimônio.')
            ->columns([
                TableColumns::text('bem.NumPatrimonio', 'Patrimônio', isFirstColumn: true)
                    ->badge()
                    ->copyable()
                    ->copyMessage('Patrimônio copiado')
                    ->weight('medium'),
                TableColumns::text('bem.Descricao', 'Descrição')
                    ->limit(45)
                    ->wrap()
                    ->tooltip(fn (Reavaliacao $record): ?string => $record->bem?->Descricao),
                TableColumns::date('data_reavaliacao', 'Data da Reavaliação'),
                TableColumns::date('data_referencia', 'Data de Referência'),
                TableColumns::date('data_disponibilizacao', 'Data de Disponibilização'),
                TableColumns::money('valor_aquisicao', 'Valor de Aquisição'),
                TableColumns::money('valor_mercado', 'Valor de Mercado'),
                TableColumns::money('valor_reavaliacao', 'Valor da Reavaliação')
                    ->weight('medium'),
                TableColumns::money('ajuste_contabil', 'Ajuste Contábil'),
                TableColumns::text('vida_util', 'Vida Útil')
                    ->suffix(' meses'),
                TableColumns::text('vida_util_reavaliacao', 'Vida Útil Reavaliação')
                    ->suffix(' meses'),
                TableColumns::text('vida_util_siafi', 'Vida Útil SIAFI')
                    ->suffix(' meses'),
                TableColumns::text('tempo_utilizacao_meses', 'Tempo de Utilização')
                    ->suffix(' meses'),
                TableColumns::text('vida_util_remanescente_meses', 'Vida Útil Remanescente')
                    ->suffix(' meses'),
                TableColumns::text('utilizacao_bem_anos', 'Utilização')
                    ->suffix(' anos'),
                TableColumns::text('vida_util_estimada_anos', 'Vida Útil Estimada')
                    ->suffix(' anos'),
                TableColumns::text('estado_conservacao', 'Estado')
                    ->badge()
                    ->color(fn (?string $state): string => match (mb_strtoupper($state ?? '')) {
                        'ÓTIMO', 'OTIMO', 'BOM' => 'success',
                        'REGULAR' => 'warning',
                        default => 'danger',
                    }),
                TableColumns::text('pub1', 'PUB1'),
                TableColumns::text('puv', 'PUV'),
                TableColumns::text('fr', 'FR'),
                TableColumns::text('responsavel.name', 'Atualizado por'),
                TableColumns::dateTime('date_time', 'Atualizado em'),
            ])
            ->filters([
                Tables\Filters\Filter::make('patrimonioFilter')
                    ->columnSpan(2)
                    ->label('Patrimônio')
                    ->form([
                        Forms\Components\Select::make('patrimonio')
                            ->label('Patrimônio')
                            ->placeholder('Busque pelo número do patrimônio')
                            ->getSearchResultsUsing(fn (string $search): array => BemMovel::query()
                                ->where('NumPatrimonio', 'like', "%{$search}%")
                                ->orWhere('Descricao', 'like', "%{$search}%")
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn (BemMovel $record): array => [
                                    $record->getKey() => "{$record->NumPatrimonio} - {$record->Descricao}",
                                ])
                                ->all())
                            ->getOptionLabelUsing(function ($value): ?string {
                                $record = BemMovel::query()->find($value);

                                return $record
                                    ? "{$record->NumPatrimonio} - {$record->Descricao}"
                                    : null;
                            })
                            ->searchable()
                            ->native(false),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when(
                            filled($data['patrimonio'] ?? null),
                            fn ($query) => $query->where('patrimonio', $data['patrimonio']),
                            fn ($query) => $query->whereRaw('1 = 0'),
                        )),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->actions([])
            ->bulkActions([])
            ->defaultSort('data_reavaliacao', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReavaliacaos::route('/'),
            'create' => Pages\CreateReavaliacao::route('/create'),
            'edit' => Pages\EditReavaliacao::route('/{record}/edit'),
        ];
    }
}
