<?php

namespace App\Filament\Resources\Patrimonio\BensMoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensMoveis\DepreciacaoResource\Pages;
use App\Filament\Support\MoneyInput;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensMoveis\BemMovel;
use App\Models\Patrimonio\BensMoveis\Depreciacao;
use Filament\Forms;
use Filament\Forms\Form;
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

    protected static ?int $navigationSort = 8;

    protected static ?string $recordTitleAttribute = 'item';

    protected static ?string $slug = 'bens-moveis/depreciacoes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identificação e Período')
                    ->description('Vincule o bem móvel e informe a referência do cálculo.')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Forms\Components\Select::make('patrimonio')
                            ->label('Patrimônio')
                            ->relationship('patrimonioRef', 'NumPatrimonio')
                            ->getOptionLabelFromRecordUsing(fn (BemMovel $record): string => "{$record->NumPatrimonio} - {$record->Descricao}")
                            ->placeholder('Busque pelo número do patrimônio')
                            ->searchable()
                            ->native(false)
                            ->optionsLimit(50)
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\DatePicker::make('data_calculo')
                            ->label('Data do Cálculo')
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->default(now())
                            ->required(),

                        Forms\Components\TextInput::make('item')
                            ->label('Item')
                            ->placeholder('Informe a referência do cálculo')
                            ->maxLength(255)
                            ->required(),

                        Forms\Components\TextInput::make('vida_util')
                            ->label('Vida Útil')
                            ->numeric()
                            ->minValue(1)
                            ->suffix('meses')
                            ->placeholder('0')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Valores da Depreciação')
                    ->description('Valores financeiros correspondentes a este período.')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        MoneyInput::make('valor')
                            ->label('Valor Base')
                            ->required(),

                        MoneyInput::make('valor_residual')
                            ->label('Valor Residual')
                            ->required(),

                        MoneyInput::make('depreciacao_mensal')
                            ->label('Depreciação Mensal')
                            ->required(),

                        MoneyInput::make('depreciacao_acumulada')
                            ->label('Depreciação Acumulada')
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
            ->modifyQueryUsing(fn ($query) => $query->with('patrimonioRef'))
            ->emptyStateIcon('heroicon-o-funnel')
            ->emptyStateHeading(fn ($livewire): string => filled(data_get($livewire, 'tableFilters.patrimonio.patrimonio'))
                ? 'Nenhuma depreciação encontrada'
                : 'Selecione um patrimônio no filtro')
            ->emptyStateDescription(fn ($livewire): string => filled(data_get($livewire, 'tableFilters.patrimonio.patrimonio'))
                ? 'Não há registros de depreciação para o patrimônio selecionado.'
                : 'A tabela de depreciações só carrega registros após a busca por um patrimônio.')
            ->columns([
                TableColumns::text('patrimonioRef.NumPatrimonio', 'Patrimônio', true)
                    ->badge()
                    ->copyable()
                    ->copyMessage('Patrimônio copiado')
                    ->weight('medium'),
                TableColumns::text('patrimonioRef.Descricao', 'Descrição')
                    ->limit(45)
                    ->wrap()
                    ->tooltip(fn (Depreciacao $record): ?string => $record->patrimonioRef?->Descricao),
                TableColumns::text('item', 'Item')
                    ->limit(35)
                    ->tooltip(fn (Depreciacao $record): ?string => $record->item),
                TableColumns::date('data_calculo', 'Data do Cálculo')
                    ->badge()
                    ->color('primary'),
                TableColumns::money('valor', 'Valor Base'),
                TableColumns::money('valor_residual', 'Valor Residual'),
                TableColumns::money('depreciacao_mensal', 'Depreciação Mensal')
                    ->weight('medium'),
                TableColumns::money('depreciacao_acumulada', 'Depreciação Acumulada'),
                TableColumns::money('valor_liquido_contabil', 'Valor Líquido Contábil')
                    ->weight('medium'),
                TableColumns::text('vida_util', 'Vida Útil')
                    ->numeric()
                    ->badge()
                    ->color('gray')
                    ->suffix(' meses'),
            ])
            ->filters([
                Tables\Filters\Filter::make('patrimonio')
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
            ->defaultSort('item', 'asc');
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
