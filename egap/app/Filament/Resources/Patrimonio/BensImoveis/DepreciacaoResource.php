<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\DepreciacaoResource\Pages;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensImoveis\Depreciacao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
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
    protected static ?string $modelLabel = 'Depreciação de Imóvel';
    protected static ?string $pluralModelLabel = 'Depreciação de Imóveis';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'bens-imoveis/depreciacao';

    public static function form(Form $form): Form
    {
        $moneyInput = fn (string $field, string $label) => Forms\Components\TextInput::make($field)
            ->label($label)
            ->prefix('R$')
            ->placeholder('0,00')
            ->mask(RawJs::make('$money($input, \',\', \'.\', 2)'))
            ->stripCharacters('.')
            ->formatStateUsing(fn ($state): ?string => filled($state) ? number_format((float) $state, 2, ',', '') : null)
            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? str_replace(',', '.', $state) : null);

        return $form
            ->schema([
                Forms\Components\Section::make('Identificação do Imóvel')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        Forms\Components\Select::make('Id_imovel')
                            ->label('Imóvel')
                            ->relationship('imovelRelacaoref', 'descricao')
                            ->searchable()
                            ->preload()
                            ->placeholder('Selecione o imóvel')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('id_obra')
                            ->label('Obras e Ampliações')
                            ->relationship('obraRelacaoref', 'descricao')
                            ->searchable()
                            ->preload()
                            ->placeholder('Selecione a obra ou ampliação')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Parâmetros do Cálculo')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Forms\Components\DatePicker::make('data_calculo')
                            ->label('Data do Cálculo')
                            ->displayFormat('d/m/Y')
                            ->native(false),

                        Forms\Components\DateTimePicker::make('date_time')
                            ->label('Atualizado em')
                            ->displayFormat('d/m/Y H:i')
                            ->native(false),

                        Forms\Components\TextInput::make('vida_util')
                            ->label('Vida Útil')
                            ->numeric()
                            ->suffix('meses')
                            ->placeholder('0'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Valores da Depreciação')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        $moneyInput('depreciacao_mensal', 'Depreciação Mensal'),
                        $moneyInput('depreciacao_acumulada', 'Depreciação Acumulada'),
                        $moneyInput('valor_residual', 'Valor Residual'),
                        $moneyInput('valor_liquido_contabil', 'Valor Líquido Contábil')
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('Id', '#', isFirstColumn: true),
                TableColumns::text('imovelRelacaoref.descricao', 'Descrição do Imóvel'),
                TableColumns::text('obraRelacaoref.descricao', 'Obras e Ampliações'),
                TableColumns::money('depreciacao_acumulada', 'Depreciação Acumulada'),
                TableColumns::money('valor_residual', 'Valor Residual'),
                TableColumns::money('valor', 'Valor'),
                TableColumns::money('valor_liquido_contabil', 'Valor Contábil Líquido'),
                TableColumns::text('vida_util', 'Vida Útil')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('Id_imovel')
                    ->label('Imóvel')
                    ->columnSpan(6)
                    ->relationship('imovelRelacaoref', 'descricao')
                    ->searchable()
                    ->preload(),
            ], layout: Tables\Enums\FiltersLayout::AboveContent);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepreciacaos::route('/'),
            'create' => Pages\CreateDepreciacao::route('/create'),
            'edit' => Pages\EditDepreciacao::route('/{record}/edit'),
        ];
    }
}
