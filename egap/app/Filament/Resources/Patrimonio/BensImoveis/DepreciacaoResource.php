<?php

namespace App\Filament\Egap\Resources\Patrimonio\BensImoveis;

use App\Filament\Egap\Clusters\PatrimonioCluster;
use App\Filament\Egap\Resources\Patrimonio\BensImoveis\DepreciacaoResource\Pages;
use App\Models\Egap\Patrimonio\BensImoveis\Depreciacao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class DepreciacaoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Depreciacao::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-down';
    protected static ?string $navigationLabel = 'Depreciação';
    protected static ?string $modelLabel = 'Depreciação Imóvel';
    protected static ?string $pluralModelLabel = 'Depreciação de Imóveis';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'bens-imoveis/depreciacao';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('item')
                            ->label('item')
                            ->numeric(),

                        Forms\Components\Select::make('Id_imovel')
                            ->label('Descrição do Imóvel')
                            ->relationship('imovelRelacaoref', 'descricao')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('id_obra')
                            ->label('Obras e Ampliações')
                            ->relationship('obraRelacaoref', 'descricao')
                            ->searchable()
                            ->preload(),

                        Forms\Components\DatePicker::make('data_calculo')
                            ->label('Data Cálculo')
                            ->displayFormat('d/m/Y'),

                        Forms\Components\TextInput::make('depreciacao_mensal')
                            ->label('Depreciacao Mensal')
                            ->numeric(),

                        Forms\Components\TextInput::make('depreciacao_acumulada')
                            ->label('Depreciacao Acumulada')
                            ->numeric(),

                        Forms\Components\TextInput::make('valor_residual')
                            ->label('Valor Residual')
                            ->numeric(),

                        Forms\Components\TextInput::make('valor')
                            ->label('Valor')
                            ->numeric(),

                        Forms\Components\DateTimePicker::make('date_time')
                            ->label('date time')
                            ->displayFormat('Y-m-d H:i'),

                        Forms\Components\TextInput::make('valor_liquido_contabil')
                            ->label('Valor Líquido Contabil')
                            ->numeric(),

                        Forms\Components\TextInput::make('vida_util')
                            ->label('Vida Útil')
                            ->numeric(),
                    ])
                    ->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item')
                    ->label('item')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('imovelRelacaoref.descricao')
                    ->label('Descrição do Imóvel')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('obraRelacaoref.descricao')
                    ->label('Obras e Ampliações')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('depreciacao_acumulada')
                    ->label('Depreciacao Acumulada')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('valor_residual')
                    ->label('Valor Residual')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('valor')
                    ->label('Valor')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('valor_liquido_contabil')
                    ->label('Valor Liquido Contabil')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('vida_util')
                    ->label('Vida Útil')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('Id_imovel')
                    ->label('Descrição do Imóvel')
                    ->relationship('imovelRelacaoref', 'descricao')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->color('warning')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Editar Depreciação Imóveis')
                    ->modalWidth('lg'),

                Tables\Actions\DeleteAction::make()
                    ->label('Excluir')
                    ->color('danger')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir Selecionados'),
                ]),
            ])
            ->searchPlaceholder('Entre com a palavra-chave')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->striped()
            ->emptyStateHeading('Nenhuma Depreciação encontrada');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepreciacaos::route('/'),
        ];
    }
}
