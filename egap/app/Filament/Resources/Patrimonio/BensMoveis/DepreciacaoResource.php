<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Models\Patrimonio\BensMoveis\Depreciacao;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class DepreciacaoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Depreciacao::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Bens Móveis';
    protected static ?string $navigationLabel = 'Depreciação';
    protected static ?string $modelLabel = 'Depreciação';
    protected static ?int $navigationSort = 6;


    /** ✅ BLOQUEIA EDIÇÃO E CRIAÇÃO: Tela apenas para visualização */
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('bem.NumPatrimonio')
                    ->label('Patrimônio')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('item')
                    ->label('Item')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('data_calculo')
                    ->label('Data Cálculo')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valor')
                    ->label('Valor')
                    ->money('BRL'),

                Tables\Columns\TextColumn::make('vida_util')
                    ->label('Vida Útil (Meses)')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('valor_residual')
                    ->label('Vlr. Residual')
                    ->money('BRL'),

                Tables\Columns\TextColumn::make('depreciacao_mensal')
                    ->label('Depr. Mensal')
                    ->money('BRL'),

                Tables\Columns\TextColumn::make('depreciacao_acumulada')
                    ->label('Depr. Acumulada')
                    ->money('BRL'),

                Tables\Columns\TextColumn::make('valor_liquido_contabil')
                    ->label('Líquido Contábil')
                    ->money('BRL')
                    ->weight('bold'),
            ])
            ->filters([
                /** ✅ Filtro para evitar carregar tudo de uma vez,
                 * similar ao "selecione ao menos um filtro" do original
                 */
                Tables\Filters\Filter::make('data_calculo')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('calculado_apos')
                            ->label('Calculado após'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['calculado_apos'], fn($q) => $q->where('data_calculo', '>=', $data['calculado_apos']));
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Patrimonio\BensMoveis\DepreciacaoResource\Pages\ListDepreciacaos::route('/'),
        ];
    }
}
