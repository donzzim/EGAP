<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensIntangiveis\AmortizacaoResource\Pages;
use App\Filament\Support\MoneyInput;
use App\Models\Patrimonio\BensIntangiveis\Amortizacao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class AmortizacaoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Amortizacao::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationLabel = 'Amortizações';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Amortização';
    protected static ?string $pluralModelLabel = 'Amortizações';
    protected static ?string $navigationGroup = 'Bens Intangíveis';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identificação e Período')
                    ->description('Vincule o bem intangível e a data base deste cálculo.')
                    ->schema([
                        Forms\Components\Select::make('id_intangivel')
                            ->label('Software')
                            ->relationship('idIntangivelRef', 'nome')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\DatePicker::make('data_calculo')
                            ->label('Data do Cálculo')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->required(),

                        Forms\Components\TextInput::make('item')
                            ->label('Item')
                            ->maxLength(255)
                            ->required(),

                        Forms\Components\TextInput::make('vida_util')
                            ->label('Vida Útil (em meses)')
                            ->numeric()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Valores da Amortização')
                    ->description('Valores financeiros correspondentes a este período.')
                    ->schema([
                        MoneyInput::make('valor')
                            ->label('Valor Base')
                            ->required(),

                        MoneyInput::make('amortizacao_mensal')
                            ->label('Amortização Mensal')
                            ->required(),

                        MoneyInput::make('amortizacao_acumulada')
                            ->label('Amortização Acumulada')
                            ->required(),

                        MoneyInput::make('valor_liquido_contabil')
                            ->label('Valor Líquido Contábil')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('idIntangivelRef.nome')
                    ->label('Software')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_calculo')
                    ->label('Data de Cálculo')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amortizacao_mensal')
                    ->label('Amortização Mensal')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amortizacao_acumulada')
                    ->label('Acumulada')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valor_liquido_contabil')
                    ->label('V. Líquido Contábil')
                    ->money('BRL')
                    ->sortable(),

                Tables\Columns\TextColumn::make('vida_util')
                    ->label('Vida Útil')
                    ->numeric()
                    ->suffix(' meses'),
            ])
            ->filters([
                // Adicione filtros aqui, se necessário (ex: SelectFilter por Bem Intangível)
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAmortizacaos::route('/'),
            'create' => Pages\CreateAmortizacao::route('/create'),
            'edit' => Pages\EditAmortizacao::route('/{record}/edit'),
        ];
    }
}
