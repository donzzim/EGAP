<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\ReavaliacaoResource\Pages;
use App\Filament\Support\TableDefaults;
use App\Filament\Support\TableColumns;
use App\Models\Patrimonio\BensImoveis\Reavaliacao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Support\Facades\DB;

class ReavaliacaoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Reavaliacao::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Reavaliação';
    protected static ?string $modelLabel = 'Reavaliação';
    protected static ?string $pluralModelLabel = 'Reavaliação';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'bens-imoveis/reavaliacoes';

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

        $monthsInput = fn (string $field, string $label) => Forms\Components\TextInput::make($field)
            ->label($label)
            ->numeric()
            ->suffix('meses')
            ->placeholder('0');

        $yearsInput = fn (string $field, string $label) => Forms\Components\TextInput::make($field)
            ->label($label)
            ->numeric()
            ->suffix('anos')
            ->placeholder('0');

        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Reavaliação')
                            ->icon('heroicon-o-banknotes')
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

                                        Forms\Components\Select::make('Id_estadoconservacao')
                                            ->label('Estado de Conservação')
                                            ->relationship('estadoConservacaoRelacaoref', 'descEstadoConservacao')
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Selecione o estado de conservação')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Dados da Reavaliação')
                                    ->icon('heroicon-o-calculator')
                                    ->schema([
                                        Forms\Components\DatePicker::make('data_reavaliacao')
                                            ->label('Data da Reavaliação')
                                            ->default(now())
                                            ->displayFormat('d/m/Y')
                                            ->native(false),

                                        $moneyInput('valor_reavaliacao', 'Valor da Reavaliação'),

                                        Forms\Components\TextInput::make('vida_util_reavaliacao')
                                            ->label('Vida Útil da Reavaliação')
                                            ->numeric()
                                            ->suffix('meses')
                                            ->placeholder('0'),

                                        $moneyInput('ajuste_contabil', 'Ajuste Contábil'),

                                        Forms\Components\Textarea::make('observacao')
                                            ->label('Observação')
                                            ->placeholder('Registre informações relevantes sobre a reavaliação')
                                            ->columnSpanFull()
                                            ->rows(4),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Complemento')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                Forms\Components\Section::make('Valores de Referência')
                                    ->icon('heroicon-o-banknotes')
                                    ->schema([
                                        $moneyInput('valor_mercado', 'Valor de Mercado'),
                                        $moneyInput('valor_aquisicao', 'Valor de Aquisição'),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Prazos e Vida Útil')
                                    ->icon('heroicon-o-calendar-days')
                                    ->schema([
                                        $monthsInput('vida_util_siafi', 'Vida Útil SIAFI'),
                                        $monthsInput('vida_util', 'Vida Útil'),
                                        $monthsInput('tempo_utilizacao_meses', 'Tempo de Utilização'),
                                        $monthsInput('vida_util_remanescente_meses', 'Vida Útil Remanescente'),
                                        $yearsInput('vida_util_estimada_anos', 'Vida Útil Estimada'),
                                        $yearsInput('utilizacao_bem_anos', 'Utilização do Bem'),
                                        $yearsInput('idade_aparente_anos', 'Idade Aparente'),
                                    ])
                                    ->columns(3),

                                Forms\Components\Section::make('Parâmetros Técnicos')
                                    ->icon('heroicon-o-adjustments-horizontal')
                                    ->schema([
                                        Forms\Components\TextInput::make('PUB1')
                                            ->label('PUB1')
                                            ->numeric()
                                            ->placeholder('0'),

                                        Forms\Components\TextInput::make('PUV')
                                            ->label('PUV')
                                            ->numeric()
                                            ->placeholder('0'),

                                        Forms\Components\TextInput::make('FR')
                                            ->label('FR')
                                            ->numeric()
                                            ->placeholder('0'),
                                    ])
                                    ->columns(3),

                                Forms\Components\Section::make('Controle')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->schema([
                                        Forms\Components\Select::make('atualizado_por')
                                            ->label('Atualizado por')
                                            ->relationship('atualizadoPorRelacaoref', 'name')
                                            ->default(fn () => auth()->id())
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Selecione o usuário'),

                                        Forms\Components\DateTimePicker::make('data_disponibilizacao')
                                            ->label('Data de Disponibilização')
                                            ->default(now())
                                            ->displayFormat('d/m/Y H:i:s')
                                            ->native(false),

                                        Forms\Components\DateTimePicker::make('data_referencia')
                                            ->label('Data de Referência')
                                            ->default(now())
                                            ->displayFormat('d/m/Y H:i:s')
                                            ->native(false),

                                        Forms\Components\DateTimePicker::make('date_time')
                                            ->label('Atualizado em')
                                            ->hidden(),
                                    ])
                                    ->columns(3),
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }
    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('Id', '#', isFirstColumn: true),
                TableColumns::text('imovelRelacaoref.descricao', 'Imóvel')
                    ->limit(45)
                    ->tooltip(fn ($record): ?string => $record->imovelRelacaoref?->descricao),
                TableColumns::date('data_reavaliacao', 'Data da Reavaliação'),
                TableColumns::money('valor_reavaliacao', 'Valor da Reavaliação'),
                TableColumns::text('vida_util_reavaliacao', 'Vida Útil')
                    ->badge()
                    ->suffix(' meses'),
                TableColumns::text('estadoConservacaoRelacaoref.descEstadoConservacao', 'Estado de Conservação'),
                TableColumns::money('ajuste_contabil', 'Ajuste Contábil'),
                TableColumns::text('observacao', 'Observação')
                    ->limit(50)
                    ->tooltip(fn ($record): ?string => $record->observacao),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReavaliacoes::route('/'),
            'create' => Pages\CreateReavaliacao::route('/create'),
            'edit' => Pages\EditReavaliacao::route('/{record}/edit'),
        ];
    }
}
