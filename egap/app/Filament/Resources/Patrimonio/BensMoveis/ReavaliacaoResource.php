<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\ReavaliacaoResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensMoveis\Reavaliacao;
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

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('patrimonio', 'Patrimônio', isFirstColumn: true)
                    ->badge(),
                TableColumns::date('data_reavaliacao', 'Última Reavaliação'),
                TableColumns::money('valor_aquisicao', 'Valor de Aquisição'),
                TableColumns::money('valor_reavaliacao', 'Valor da Reavaliação')
                    ->weight('medium'),
                TableColumns::text('vida_util_remanescente_meses', 'Vida Útil Remanescente')
                    ->suffix(' meses'),
                TableColumns::text('estado_conservacao', 'Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ÓTIMO', 'BOM' => 'success',
                        'REGULAR' => 'warning',
                        default => 'danger',
                    }),

                TableColumns::money('ajuste_contabil', 'Ajuste Contábil'),
                TableColumns::text('responsavel.name', 'Atualizado por')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado_conservacao')
                    ->options([
                        'ÓTIMO' => 'ÓTIMO',
                        'BOM' => 'BOM',
                        'REGULAR' => 'REGULAR',
                        'RUIM' => 'RUIM',
                    ]),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->actions([])
            ->bulkActions([])
            ->defaultSort('data_reavaliacao', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReavaliacaos::route('/'),
        ];
    }
}
