<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use App\Filament\Resources\Patrimonio\BensIntangiveis\AmortizacaoResource\Pages\ListAmortizacaos;
use App\Filament\Resources\Patrimonio\BensIntangiveis\AmortizacaoResource\Pages\CreateAmortizacao;
use App\Filament\Resources\Patrimonio\BensIntangiveis\AmortizacaoResource\Pages\EditAmortizacao;
use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensIntangiveis\AmortizacaoResource\Pages;
use App\Filament\Support\MoneyInput;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensIntangiveis\Amortizacao;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AmortizacaoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Amortizacao::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Amortizações';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Amortização';

    protected static ?string $pluralModelLabel = 'Amortizações';

    protected static string | \UnitEnum | null $navigationGroup = 'Bens Intangíveis';

    protected static ?string $slug = 'bens-intangiveis/amortizacoes';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificação e Período')
                    ->description('Vincule o bem intangível e a data base deste cálculo.')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Select::make('id_intangivel')
                            ->label('Bem Intangível')
                            ->relationship('idIntangivelRef', 'nome')
                            ->placeholder('Selecione o bem intangível')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required(),

                        DatePicker::make('data_calculo')
                            ->label('Data do Cálculo')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->required(),

                        TextInput::make('item')
                            ->label('Item')
                            ->placeholder('Informe a referência do cálculo')
                            ->maxLength(255)
                            ->required(),

                        TextInput::make('vida_util')
                            ->label('Vida Útil (em meses)')
                            ->numeric()
                            ->minValue(1)
                            ->suffix('meses')
                            ->placeholder('0')
                            ->required(),
                    ])->columns(2),

                Section::make('Valores da Amortização')
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
                SelectFilter::make('id_intangivel')
                    ->label('Bem Intangível')
                    ->relationship('idIntangivelRef', 'nome')
                    ->searchable()
                    ->preload(),
            ], layout: FiltersLayout::AboveContent)
            ->defaultSort('data_calculo', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAmortizacaos::route('/'),
            'create' => CreateAmortizacao::route('/create'),
            'edit' => EditAmortizacao::route('/{record}/edit'),
        ];
    }
}
