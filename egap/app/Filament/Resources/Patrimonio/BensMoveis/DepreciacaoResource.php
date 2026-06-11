<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\DepreciacaoResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensMoveis\Depreciacao;
use Filament\Forms;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DepreciacaoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Depreciacao::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Bens Móveis';

    protected static ?string $navigationLabel = 'Depreciação';

    protected static ?string $modelLabel = 'Depreciação';

    protected static ?string $pluralModelLabel = 'Depreciações';

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'bens-moveis/depreciacoes';

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
                TableColumns::text('bem.NumPatrimonio', 'Patrimônio', isFirstColumn: true)
                    ->badge(),
                TableColumns::text('item', 'Item')
                    ->limit(40)
                    ->tooltip(fn ($record): ?string => $record->item),
                TableColumns::date('data_calculo', 'Data do Cálculo'),
                TableColumns::money('valor', 'Valor'),
                TableColumns::text('vida_util', 'Vida Útil')
                    ->badge()
                    ->color('gray')
                    ->suffix(' meses'),
                TableColumns::money('valor_residual', 'Valor Residual'),
                TableColumns::money('depreciacao_mensal', 'Depreciação Mensal'),
                TableColumns::money('depreciacao_acumulada', 'Depreciação Acumulada'),
                TableColumns::money('valor_liquido_contabil', 'Valor Líquido Contábil')
                    ->weight('medium'),
            ])
            ->filters([
                Tables\Filters\Filter::make('data_calculo')
                    ->form([
                        Forms\Components\DatePicker::make('calculado_apos')
                            ->label('Calculado após')
                            ->displayFormat('d/m/Y')
                            ->native(false),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['calculado_apos'], fn ($q) => $q->where('data_calculo', '>=', $data['calculado_apos']));
                    }),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->tooltip('Visualizar')
                    ->hiddenLabel(),
            ])
            ->bulkActions([])
            ->defaultSort('data_calculo', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepreciacaos::route('/'),
        ];
    }
}
