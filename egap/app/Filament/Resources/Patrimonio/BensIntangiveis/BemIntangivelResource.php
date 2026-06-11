<?php

namespace App\Filament\Resources\Patrimonio\BensIntangiveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensIntangiveis\BemIntangivelResource\Pages;
use App\Filament\Support\MoneyInput;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Patrimonio\BensIntangiveis\BemIntangivel;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BemIntangivelResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = BemIntangivel::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Administração';

    protected static ?string $modelLabel = 'Bem Intangível';

    protected static ?string $pluralModelLabel = 'Administração dos Bens Intangíveis';

    protected static ?string $navigationGroup = 'Bens Intangíveis';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'bens-intangiveis/bens';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Cadastro do Bem Intangível')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tabs\Tab::make('Intangível')
                            ->icon('heroicon-o-cube')
                            ->schema([
                                Forms\Components\Section::make('Informações Principais')
                                    ->description('Dados básicos de identificação do bem intangível.')
                                    ->icon('heroicon-o-identification')
                                    ->schema([
                                        Forms\Components\Select::make('id_tipointangivel')
                                            ->label('Tipo de Intangível')
                                            ->relationship('idTipoIntangivelRef', 'descricao')
                                            ->placeholder('Selecione o tipo')
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->required(),

                                        Forms\Components\TextInput::make('nome')
                                            ->label('Nome')
                                            ->placeholder('Ex: Licença de Software')
                                            ->maxLength(255)
                                            ->required(),

                                        Forms\Components\Select::make('id_fabricante')
                                            ->label('Fabricante')
                                            ->relationship('idFabricanteRef', 'descricao')
                                            ->placeholder('Selecione o fabricante')
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->required(),

                                        Forms\Components\Select::make('classificacao')
                                            ->label('Classificação')
                                            ->options([
                                                'Desktops' => 'Desktops',
                                                'Servidores' => 'Servidores',
                                            ])
                                            ->placeholder('Selecione a classificação')
                                            ->native(false)
                                            ->required(),
                                    ])->columns(2),

                                Forms\Components\Section::make('Detalhes Técnicos')
                                    ->description('Informe a versão e a quantidade de licenças ou unidades.')
                                    ->icon('heroicon-o-cpu-chip')
                                    ->schema([
                                        Forms\Components\TextInput::make('versao')
                                            ->label('Versão')
                                            ->placeholder('Ex: 2.0')
                                            ->maxLength(255)
                                            ->required(),

                                        Forms\Components\TextInput::make('quantidade')
                                            ->label('Quantidade')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->suffix('unidade(s)')
                                            ->required(),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Contábil')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Forms\Components\Section::make('Classificação e Registro')
                                    ->description('Vincule o bem às classificações contábeis e patrimoniais.')
                                    ->icon('heroicon-o-clipboard-document-check')
                                    ->schema([
                                        Forms\Components\Select::make('id_planocontas')
                                            ->label('Conta Contábil')
                                            ->relationship('idPlanoContasRef', 'titulo')
                                            ->placeholder('Selecione a conta contábil')
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->required(),

                                        Forms\Components\Select::make('id_elementodespesa')
                                            ->label('Elemento de Despesa')
                                            ->relationship('idElementoDespesaRef', 'DescricaodaClasse')
                                            ->placeholder('Selecione o elemento de despesa')
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->required(),

                                        Forms\Components\TextInput::make('inscricao_generica')
                                            ->label('Inscrição Genérica')
                                            ->placeholder('Informe a inscrição genérica')
                                            ->maxLength(255)
                                            ->required(),

                                        Forms\Components\TextInput::make('nota_patrimonial')
                                            ->label('Nota Patrimonial')
                                            ->placeholder('Informe a nota patrimonial')
                                            ->maxLength(255)
                                            ->required(),
                                    ])->columns(2),

                                Forms\Components\Section::make('Dados da Aquisição')
                                    ->description('Registre a origem, a data e o valor de aquisição.')
                                    ->icon('heroicon-o-shopping-cart')
                                    ->schema([
                                        Forms\Components\TextInput::make('processo_aquisicao')
                                            ->label('Processo de Aquisição')
                                            ->placeholder('Informe o número do processo')
                                            ->maxLength(255)
                                            ->required(),

                                        Forms\Components\DatePicker::make('data_aquisicao')
                                            ->label('Data de Aquisição')
                                            ->default(now())
                                            ->displayFormat('d/m/Y')
                                            ->native(false)
                                            ->closeOnDateSelection()
                                            ->required(),

                                        MoneyInput::make('valor_aquisicao')
                                            ->label('Valor de Aquisição')
                                            ->required(),
                                    ])->columns(3),

                                Forms\Components\Section::make('Valores e Amortização')
                                    ->description('Informe a vida útil e os valores contábeis atualizados.')
                                    ->icon('heroicon-o-calculator')
                                    ->schema([
                                        Forms\Components\TextInput::make('vida_util_remanescente')
                                            ->label('Vida Útil Remanescente (meses)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->suffix('meses')
                                            ->placeholder('0')
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
                            ]),

                        Tabs\Tab::make('Situação')
                            ->icon('heroicon-o-chat-bubble-left-ellipsis')
                            ->schema([
                                Forms\Components\Section::make('Informações Adicionais')
                                    ->description('Registre informações complementares relevantes para o bem.')
                                    ->icon('heroicon-o-chat-bubble-left-right')
                                    ->schema([
                                        Forms\Components\Textarea::make('observacao')
                                            ->label('Observação')
                                            ->placeholder('Insira detalhes ou observações sobre o status deste bem...')
                                            ->rows(6)
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('nome', 'Bem Intangível', isFirstColumn: true)
                    ->description(fn (BemIntangivel $record): ?string => $record->versao ? "Versão {$record->versao}" : null)
                    ->icon('heroicon-o-cube')
                    ->weight('medium')
                    ->wrap(),
                TableColumns::text('idTipoIntangivelRef.descricao', 'Tipo')
                    ->badge()
                    ->color('info'),
                TableColumns::text('idFabricanteRef.descricao', 'Fabricante')
                    ->wrap(),
                TableColumns::text('classificacao', 'Classificação')
                    ->badge()
                    ->color('gray'),
                TableColumns::text('quantidade', 'Quantidade')
                    ->numeric()
                    ->badge()
                    ->color('gray'),
                TableColumns::date('data_aquisicao', 'Aquisição'),
                TableColumns::money('valor_aquisicao', 'Valor de Aquisição')
                    ->weight('medium'),
                TableColumns::text('processo_aquisicao', 'Processo de Aquisição')
                    ->limit(15)
                    ->tooltip(fn (BemIntangivel $record): ?string => $record->processo_aquisicao)
                    ->copyable()
                    ->copyMessage('Processo copiado'),
                TableColumns::text('inscricao_generica', 'Inscrição Genérica'),
                TableColumns::text('atualizadoPorRef.name', 'Atualizado por'),
                TableColumns::date('atualizado_em', 'Atualizado em'),
                TableColumns::text('observacao', 'Observação')
                    ->limit(50)
                    ->tooltip(fn (BemIntangivel $record): ?string => $record->observacao)
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_tipointangivel')
                    ->label('Tipo de Intangível')
                    ->relationship('idTipoIntangivelRef', 'descricao')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('id_fabricante')
                    ->label('Fabricante')
                    ->relationship('idFabricanteRef', 'descricao')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('classificacao')
                    ->label('Classificação')
                    ->options([
                        'Desktops' => 'Desktops',
                        'Servidores' => 'Servidores',
                    ]),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBemIntangivels::route('/'),
            'create' => Pages\CreateBemIntangivel::route('/create'),
            'edit' => Pages\EditBemIntangivel::route('/{record}/edit'),
        ];
    }
}
