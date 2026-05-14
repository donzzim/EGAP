<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensMoveis;

use App\Filament\Egap\Clusters\PatrimonioCluster;
use App\Filament\Egap\Resources\Patrimonio\BensMoveis\ReavaliacaoResource\Pages;
use App\Models\Egap\Patrimonio\BensMoveis\Reavaliacao;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class ReavaliacaoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Reavaliacao::class;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = 'Bens Móveis';
    protected static ?string $navigationLabel = 'Reavaliação';
    protected static ?string $modelLabel = 'Reavaliação';
    protected static ?int $navigationSort = 7;


    /** ✅ TRAVA DE SEGURANÇA: Apenas Consulta */
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bem.NumPatrimonio')
                    ->label('Patrimônio')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_reavaliacao')
                    ->label('Última Reavaliação')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valor_aquisicao')
                    ->label('Valor Aquisição')
                    ->money('BRL'),

                Tables\Columns\TextColumn::make('valor_reavaliacao')
                    ->label('Valor Reavaliação')
                    ->money('BRL')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('vida_util_remanescente_meses')
                    ->label('Vida Útil Rem. (Meses)')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('estado_conservacao')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ÓTIMO', 'BOM' => 'success',
                        'REGULAR' => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('ajuste_contabil')
                    ->label('Ajuste Contábil')
                    ->money('BRL'),

                Tables\Columns\TextColumn::make('responsavel.name')
                    ->label('Atualizado por')
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReavaliacaos::route('/'),
        ];
    }
}
