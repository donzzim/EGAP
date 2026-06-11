<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensIntangiveis\AmortizacaoResource\Pages;
use App\Filament\Support\MoneyInput;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensIntangiveis\Amortizacao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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

    protected static ?string $slug = 'bens-intangiveis/amortizacoes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identificação e Período')
                    ->description('Vincule o bem intangível e a data base deste cálculo.')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Forms\Components\Select::make('id_intangivel')
                            ->label('Bem Intangível')
                            ->relationship('idIntangivelRef', 'nome')
                            ->placeholder('Selecione o bem intangível')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required(),

                        Forms\Components\DatePicker::make('data_calculo')
                            ->label('Data do Cálculo')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->required(),

                        Forms\Components\TextInput::make('item')
                            ->label('Item')
                            ->placeholder('Informe a referência do cálculo')
                            ->maxLength(255)
                            ->required(),

                        Forms\Components\TextInput::make('vida_util')
                            ->label('Vida Útil (em meses)')
                            ->numeric()
                            ->minValue(1)
                            ->suffix('meses')
                            ->placeholder('0')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Valores da Amortização')
                    ->description('Valores financeiros correspondentes a este período.')
                    ->icon('heroicon-o-banknotes')
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
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('idIntangivelRef.nome', 'Bem Intangível', isFirstColumn: true)
                    ->icon('heroicon-o-cube')
                    ->weight('medium')
                    ->wrap(),
                TableColumns::date('data_calculo', 'Data de Cálculo'),
                TableColumns::money('valor', 'Valor Base'),
                TableColumns::money('amortizacao_mensal', 'Amortização Mensal')
                    ->weight('medium'),
                TableColumns::money('amortizacao_acumulada', 'Amortização Acumulada'),
                TableColumns::money('valor_liquido_contabil', 'Valor Líquido Contábil'),
                TableColumns::text('vida_util', 'Vida Útil')
                    ->numeric()
                    ->badge()
                    ->color('gray')
                    ->suffix(' meses'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_intangivel')
                    ->label('Bem Intangível')
                    ->relationship('idIntangivelRef', 'nome')
                    ->searchable()
                    ->preload(),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->defaultSort('data_calculo', 'desc');
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
