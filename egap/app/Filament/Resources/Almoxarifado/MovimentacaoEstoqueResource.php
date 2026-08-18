<?php

namespace App\Filament\Resources\Almoxarifado;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Almoxarifado\MovimentacaoEstoqueResource\Pages\ListMovimentacaoEstoques;
use App\Filament\Resources\Almoxarifado\MovimentacaoEstoqueResource\Pages\CreateMovimentacaoEstoque;
use App\Filament\Resources\Almoxarifado\MovimentacaoEstoqueResource\Pages\EditMovimentacaoEstoque;
use App\Filament\Clusters\AlmoxarifadoCluster;
use App\Filament\Resources\Almoxarifado\MovimentacaoEstoqueResource\Pages;
use App\Filament\Support\MoneyInput;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Almoxarifado\MovimentacaoEstoque;
use App\Models\Almoxarifado\NotaFiscal;
use App\Models\Almoxarifado\TipoMovimentacaoNotaFiscal;
use App\Models\Cadastro\Setores;
use App\Models\User;
use App\Models\UserEgap;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MovimentacaoEstoqueResource extends Resource
{
    protected static ?string $model = MovimentacaoEstoque::class;

    protected static ?string $slug = 'movimentacao-estoque';

    protected static ?string $cluster = AlmoxarifadoCluster::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Movimentação de Estoque';

    protected static ?string $modelLabel = 'Movimentação de Estoque';

    protected static ?string $pluralModelLabel = 'Movimentações de Estoque';

    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('TabsMovimentacaoEstoque')
                ->columnSpanFull()
                ->tabs([
                    self::tabDadosMovimentacao(),
                    self::tabMaterialValores(),
                    self::tabSaldoEstoque(),
                    self::tabResponsabilidadeDestino(),
                ]),
        ]);
    }

    private static function tabDadosMovimentacao(): Tab
    {
        return Tab::make('Dados da Movimentação')
            ->icon('heroicon-o-arrows-right-left')
            ->schema([
                Section::make('Dados da movimentação')
                    ->description('Identifique a data, o tipo de movimentação e o documento fiscal relacionado.')
                    ->icon('heroicon-o-arrows-right-left')
                    ->schema([
                        DateTimePicker::make('date_time')
                            ->label('Data/Hora')
                            ->required()
                            ->seconds(false)
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->default(now())
                            ->prefixIcon('heroicon-o-calendar'),

                        Select::make('tipo_movimentacao')
                            ->label('Tipo de Movimentação')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Selecione o tipo')
                            ->prefixIcon('heroicon-o-arrows-right-left')
                            ->options(fn () => TipoMovimentacaoNotaFiscal::query()
                                ->orderBy('descricao')
                                ->pluck('descricao', 'id')
                                ->toArray()
                            ),

                        Select::make('nota_fiscal')
                            ->label('Nota Fiscal')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Selecione a nota fiscal')
                            ->prefixIcon('heroicon-o-document-text')
                            ->options(fn () => NotaFiscal::query()
                                ->orderByDesc('id')
                                ->pluck('num_documento', 'id')
                                ->toArray()
                            ),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 3,
                    ]),
            ]);
    }

    private static function tabMaterialValores(): Tab
    {
        return Tab::make('Material e Valores')
            ->icon('heroicon-o-cube')
            ->schema([
                Section::make('Material e valores da movimentação')
                    ->description('Informe o item movimentado, a quantidade e os valores da operação.')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Select::make('material')
                            ->label('Material')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->placeholder('Busque pela descrição do material')
                            ->relationship('materialRel', 'descricao_detalhada')
                            ->columnSpanFull(),

                        TextInput::make('quantidade')
                            ->label('Quantidade')
                            ->numeric()
                            ->columnSpan(1)
                            ->inputMode('decimal')
                            ->placeholder('0,00')
                            ->prefixIcon('heroicon-o-cube-transparent')
                            ->required(),

                        MoneyInput::make('preco_unitario')
                            ->label('Preço Unitário')
                            ->required()
                            ->columnSpan(1)
                            ->placeholder('0,00'),

                        MoneyInput::make('valor_total')
                            ->label('Valor Total')
                            ->required()
                            ->columnSpan(1)
                            ->placeholder('0,00')
                            ->helperText('Pode ser preenchido manualmente ou calculado automaticamente.'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 3,
                    ]),
            ]);
    }

    private static function tabSaldoEstoque(): Tab
    {
        return Tab::make('Saldo em Estoque')
            ->icon('heroicon-o-chart-bar-square')
            ->schema([
                Section::make('Saldo em estoque')
                    ->description('Registre a posição do estoque após a movimentação para manter o histórico de custo médio.')
                    ->icon('heroicon-o-chart-bar-square')
                    ->schema([
                        TextInput::make('quantidade_estoque')
                            ->label('Quantidade em Estoque')
                            ->required()
                            ->numeric()
                            ->inputMode('decimal')
                            ->placeholder('0,00')
                            ->prefixIcon('heroicon-o-archive-box'),

                        MoneyInput::make('preco_medio_estoque')
                            ->label('Preço Médio Estoque')
                            ->required()
                            ->placeholder('0,00'),

                        MoneyInput::make('valor_total_estoque')
                            ->label('Valor Total Estoque')
                            ->required()
                            ->placeholder('0,00'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 3,
                    ]),
            ]);
    }

    private static function tabResponsabilidadeDestino(): Tab
    {
        return Tab::make('Responsabilidade e Destino')
            ->icon('heroicon-o-building-office-2')
            ->schema([
                Section::make('Responsabilidade e destino')
                    ->description('Defina o setor vinculado à movimentação e o usuário responsável pela atualização.')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Select::make('id_setor')
                            ->label('Setor')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Selecione o setor')
                            ->prefixIcon('heroicon-o-map-pin')
                            ->options(fn () => Setores::query()
                                ->orderBy('Setor')
                                ->pluck('Setor', 'id')
                                ->toArray()
                            ),

                        Select::make('atualizado_por')
                            ->label('Atualizado por')
                            ->required()
                            ->default(fn () => filament()->auth()->id())
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Selecione o usuário')
                            ->prefixIcon('heroicon-o-user')
                            ->options(fn () => UserEgap::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray()
                            ),

                        Placeholder::make('info_calculo')
                            ->label('Observação')
                            ->content('Os campos monetários e quantitativos podem ser usados para rastrear o histórico do estoque e o custo médio.')
                            ->columnSpanFull(),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return TableDefaults::apply($table)
            ->query(
                static::getEloquentQuery()->latest('id')
            )
            ->columns([
                TableColumns::text('id', 'ID'),

                TableColumns::text('tipoMovimentacaoRel.descricao', 'Tipo Mov.'),

                TableColumns::dateTime('date_time', 'Data', 'd/m/Y'),

                TableColumns::text('notaFiscal.num_documento', 'Nota Fiscal'),

                TableColumns::text('materialRel.descricao_detalhada', 'Material')
                    ->wrap(),

                TableColumns::text('quantidade', 'Qtd.'),

                TableColumns::money('preco_unitario', 'Preço Unit.', divideBy: true),

                TableColumns::money('valor_total', 'Valor Total', divideBy: true),

                TableColumns::text('quantidade_estoque', 'Qtd. Estoque'),

                TableColumns::money('preco_medio_estoque', 'Preço Médio', divideBy: true),

                TableColumns::money('valor_total_estoque', 'Total Estoque', divideBy: true),

                TableColumns::text('setor.UnidadeOrganizacional', 'Unidade Judiciária'),

                TableColumns::text('setor.Setor', 'Setor'),

                TableColumns::text('pedido.id', 'Pedido'),

                TableColumns::updatedBy('atualizadoPor.name'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMovimentacaoEstoques::route('/'),
            'create' => CreateMovimentacaoEstoque::route('/create'),
            'edit' => EditMovimentacaoEstoque::route('/{record}/edit'),
        ];
    }
}
