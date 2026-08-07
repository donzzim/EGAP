<?php

namespace App\Filament\Resources\Almoxarifado;

use App\Filament\Clusters\AlmoxarifadoCluster;
use App\Filament\Resources\Almoxarifado\PedidosResource\Pages\CreatePedidos;
use App\Filament\Resources\Almoxarifado\PedidosResource\Pages\EditPedidos;
use App\Filament\Resources\Almoxarifado\PedidosResource\Pages\ListPedidos;
use App\Filament\Resources\Almoxarifado\PedidosResource\Pages\PrintPedido;
use App\Models\Almoxarifado\Pedidos;
use App\Models\Almoxarifado\SituacaoPedido;
use App\Models\Cadastro\Setores;
use App\Models\UserEgap;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PedidosResource extends Resource
{
    protected static ?string $model = Pedidos::class;

    protected static ?string $cluster = AlmoxarifadoCluster::class;

    protected static ?string $slug = 'pedidos';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $modelLabel = 'Pedido';

    protected static ?string $pluralModelLabel = 'Pedidos';

    protected static ?string $navigationLabel = 'Requisição de Materiais';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('TabsPedido')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Dados Gerais')
                        ->icon('heroicon-m-information-circle')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('num_protocolo')
                                        ->label('Núm. Protocolo')
                                        ->numeric()
                                        ->mask('****.**.***.***')
                                        ->maxLength(255),

                                    DatePicker::make('date_time')
                                        ->label('Data pedido')
                                        ->default(fn () => now())
                                        ->displayFormat('d/m/Y')
                                        ->native(false),

                                    Select::make('idSituacao')
                                        ->label('Situação')
                                        ->relationship('situacao', 'Descricao')
                                        ->searchable()
                                        ->preload()
                                        ->native(false),
                                ]),

                            Grid::make(2)
                                ->schema([
                                    Select::make('Solicitante')
                                        ->label('Solicitante')
                                        ->required()
                                        ->relationship('solicitante_get', 'name')
                                        ->default(fn () => filament()->auth()->id())
                                        ->searchable()
                                        ->preload()
                                        ->native(false),
                                    Select::make('ResponsavelAtendimento')
                                        ->label('Responsável pelo Atendimento')
                                        ->relationship('responsavel_atendimento', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->native(false),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    Select::make('UnidadeJudiciaria')
                                        ->label('Unidade Judiciária')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->options(fn () => Setores::query()
                                            ->select('CodigoPai', 'UnidadeOrganizacional', 'ordem')
                                            ->distinct('CodigoPai')
                                            ->orderBy('ordem')
                                            ->pluck('UnidadeOrganizacional', 'CodigoPai')
                                            ->toArray()
                                        )
                                        ->afterStateUpdated(fn (Set $set) => $set('Setor', null)),
                                    Select::make('Setor')
                                        ->label('Setor')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->options(fn (Get $get) => Setores::query()
                                            ->when(
                                                $get('UnidadeJudiciaria'),
                                                fn ($query, $codigoPai) => $query->where('CodigoPai', $codigoPai)
                                            )
                                            ->orderBy('Setor')
                                            ->pluck('Setor', 'id')
                                            ->toArray()
                                        )
                                        ->disabled(fn (Get $get) => blank($get('UnidadeJudiciaria'))),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    Select::make('setor_responsavel')
                                        ->label('Setor Responsável')
                                        ->relationship('setorResponsavel', 'Setor')
                                        ->searchable()
                                        ->preload()
                                        ->native(false),
                                    Select::make('ComplementoSetor')
                                        ->label('Complemento Setor')
                                        ->relationship('complementoSetor', 'descricao')
                                        ->searchable()
                                        ->preload()
                                        ->native(false),
                                ]),
                            FileUpload::make('arquivo')
                                ->label('Arquivo')
                                ->directory('pedidos')
                                ->disk('public')
                                ->visibility('public')
                                ->columnSpanFull(),
                            Textarea::make('Observacao')
                                ->label('Observação')
                                ->columnSpanFull()
                                ->rows(3),

                            Textarea::make('justificativa')
                                ->label('Justificativa')
                                ->columnSpanFull()
                                ->rows(3),
                        ]),
                    Tab::make('Itens do Pedido')
                        ->icon('heroicon-m-shopping-cart')
                        ->schema([
                            TextInput::make('valor_total_pedido')
                                ->label('Valor Total do Pedido')
                                ->readOnly()
                                ->dehydrated(false)
                                ->default(0)
                                ->prefix('R$')
                                ->extraInputAttributes(['class' => 'text-xl font-bold']),
                            Repeater::make('itens')
                                ->relationship('itens')
                                ->label('Itens do Pedido')
                                ->addActionLabel('Adicionar novo item')
                                ->columns(12)
                                ->live()
                                ->schema([
                                    Select::make('material')
                                        ->label('Material')
                                        ->relationship('materialRel', 'Descricao')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->native(false)
                                        ->columnSpan(6),
                                    TextInput::make('QuantidadeMaterial')
                                        ->label('Quantidade Solicitada')
                                        ->required()
                                        ->numeric()
                                        ->live(onBlur: true)
                                        ->columnSpan(2),
                                    TextInput::make('QuantidadeMaterialAtendida')
                                        ->label('Qtd. Atendida')
                                        ->numeric()
                                        ->live(onBlur: true)
                                        ->columnSpan(2),
                                    TextInput::make('valor_material')
                                        ->label('Valor Material')
                                        ->numeric()
                                        ->inputMode('decimal')
                                        ->prefix('R$')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            self::atualizarTotalPedido($get, $set);
                                        })
                                        ->columnSpan(2),
                                    Textarea::make('ObservacaoItem')
                                        ->label('Observação do Item')
                                        ->rows(2)
                                        ->columnSpan(6),
                                    Select::make('DescricaoDetalhada')
                                        ->label('Descrição Detalhada')
                                        ->relationship('descricaoDetalhadaRel', 'descricao_detalhada')
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->columnSpan(4),
                                    TextInput::make('situacao')
                                        ->label('Situação')
                                        ->numeric()
                                        ->columnSpan(2),
                                    TextInput::make('quantidade_validada')
                                        ->label('Quantidade Validada')
                                        ->maxLength(255)
                                        ->columnSpan(3),
                                    DatePicker::make('data_validacao')
                                        ->label('Data Validação')
                                        ->displayFormat('d/m/Y')
                                        ->native(false)
                                        ->columnSpan(3),
                                    Select::make('validado_por')
                                        ->label('Validado Por')
                                        ->relationship('validadoPor', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->columnSpan(3),
                                    Select::make('cancelado_por')
                                        ->label('Cancelado Por')
                                        ->relationship('canceladoPor', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->columnSpan(3),
                                    DatePicker::make('data_cancelado')
                                        ->label('Data Cancelamento')
                                        ->displayFormat('d/m/Y')
                                        ->native(false)
                                        ->columnSpan(3),

                                    Textarea::make('justificativa')
                                        ->label('Justificativa do Item')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ])
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::atualizarTotalPedido($get, $set))
                                ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => self::mutateItemData($data))
                                ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => self::mutateItemData($data))
                                ->columnSpanFull(),
                        ]),
                ]),
        ]);
    }

    public static function mutateItemData(array $data): array
    {
        $data['date_time'] = now();

        if (blank($data['validado_por'] ?? null) && filled(auth()->id()) && filled($data['data_validacao'] ?? null)) {
            $data['validado_por'] = auth()->id();
        }

        if (blank($data['cancelado_por'] ?? null) && filled($data['data_cancelado'] ?? null) && filled(auth()->id())) {
            $data['cancelado_por'] = auth()->id();
        }

        if (isset($data['valor_material'])) {
            $data['valor_material'] = number_format(
                self::normalizarValorMonetario($data['valor_material']),
                2,
                ',',
                '.'
            );
        }

        return $data;
    }

    public static function atualizarTotalPedido(Get $get, Set $set): void
    {
        $itens = $get('../../itens') ?? [];

        $totalGeral = collect($itens)
            ->sum(fn (array $item) => self::normalizarValorMonetario($item['valor_material'] ?? 0));

        $set('../../valor_total_pedido', number_format($totalGeral, 2, '.', ''));
    }

    public static function calcularValorTotal(array $itens): string
    {
        $total = collect($itens)
            ->sum(fn (array $item) => self::normalizarValorMonetario($item['valor_material'] ?? 0));

        return number_format($total, 2, '.', '');
    }

    public static function normalizarValorMonetario(float|int|string|null $valor): float
    {
        if ($valor === null || $valor === '') {
            return 0.0;
        }

        $valor = preg_replace('/[^\d,.-]/', '', (string) $valor) ?? '0';

        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return (float) $valor;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('id')
                    ->label('Pedido Nº')
                    ->alignCenter()
                    ->sortable('desc')
                    ->searchable(),

                TextColumn::make('date_time')
                    ->label('Data pedido')
                    ->date('d/m/Y')
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('arquivo')
                    ->label('Requisição')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => $state ? 'Abrir PDF' : '-')
                    ->url(function ($record) {
                        if (! $record->arquivo) {
                            return null;
                        }

                        return 'https://sistemas.tjes.jus.br/patrimonio'.$record->arquivo;
                    })
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->weight('bold'),

                TextColumn::make('situacao.Descricao')
                    ->label('Situação')
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('solicitante_get.name')
                    ->label('Solicitante')
                    ->alignCenter()
                    ->default(' - ')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                TextColumn::make('unidade_judiciaria.UnidadeOrganizacional')
                    ->label('Unidade Judiciária')
                    ->alignCenter()
                    ->default(' - ')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                TextColumn::make('setor_get.Setor')
                    ->label('Setor')
                    ->alignCenter()
                    ->default(' - ')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                TextColumn::make('Observacao')
                    ->label('Observação')
                    ->limit(50)
                    ->alignCenter()
                    ->default(' - ')
                    ->sortable(),

                TextColumn::make('responsavel_atendimento.name')
                    ->label('Impresso/Atendido por')
                    ->alignCenter()
                    ->default(' - ')
                    ->sortable(),

            ])
            ->filters([
                SelectFilter::make('idSituacao')
                    ->label('Situação do Pedido')
                    ->options(
                        SituacaoPedido::whereIn('id', [3, 4, 6, 7])
                            ->pluck('Descricao', 'id')
                            ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('ResponsavelAtendimento')
                    ->label('Impresso/Atendido por')
                    ->options(fn () => UserEgap::query()
                        ->whereIn(
                            'id',
                            Pedidos::query()
                                ->whereNotNull('ResponsavelAtendimento')
                                ->distinct()
                                ->pluck('ResponsavelAtendimento')
                        )
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn (Builder $query) => $query->where('ResponsavelAtendimento', $data['value'])
                        );
                    })
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('itens')
                    ->label('Material')
                    ->relationship(
                        'itens.materialRel',
                        'Descricao',
                        fn ($query) => $query->where('id_tipo_material', 'C')
                    )
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('UnidadeJudiciaria')
                    ->label('Unidade Judiciária')
                    ->options(fn () => Setores::query()
                        ->whereColumn('id', 'CodigodaUO')
                        ->orderBy('UnidadeOrganizacional')
                        ->pluck('UnidadeOrganizacional', 'CodigoPai')
                        ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['value'] ?? null),
                            fn (Builder $query) => $query->where('UnidadeJudiciaria', $data['value'])
                        );
                    })
                    ->searchable()
                    ->preload()
                    ->native(false),

                Filter::make('numero_pedido')
                    ->label('Nº Pedido')
                    ->schema([
                        TextInput::make('pedido')
                            ->label('Nº Pedido')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['pedido'] ?? null),
                            fn (Builder $query) => $query->where('id', $data['pedido'])
                        );
                    }),

                Filter::make('data_validacao')
                    ->label('Data Validação')
                    ->schema([
                        DatePicker::make('data_validacao')
                            ->label('Data Validação')
                            ->displayFormat('d/m/Y')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['data_validacao'] ?? null),
                            fn (Builder $query) => $query->whereHas(
                                'itens',
                                fn (Builder $subQuery) => $subQuery->whereDate('data_validacao', $data['data_validacao'])
                            )
                        );
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(6)
            ->recordActions([
                ViewAction::make()
                    ->hiddenLabel()
                    ->tooltip('Visualizar'),

                Action::make('impressao')
                    ->label('Impressão')
                    ->icon('heroicon-m-printer')
                    ->color('danger')
                    ->hiddenLabel()
                    ->tooltip('Impressão')
                    ->url(fn (Pedidos $record): string => route('impressao_pedido', $record))
                    ->openUrlInNewTab(),

                Action::make('anexar_requisicao')
                    ->icon('heroicon-m-paper-clip')
                    ->color('info')
                    ->hiddenLabel()
                    ->tooltip('Anexar Requisição')
                    ->requiresConfirmation()
                    ->action(function (Pedidos $record): void {
                        $record->update(['idSituacao' => 2]);
                    }),

                Action::make('encaminhar_logistica')
                    ->icon('heroicon-m-truck')
                    ->color('warning')
                    ->hiddenLabel()
                    ->tooltip('Encaminhar para Logística')
                    ->requiresConfirmation()
                    ->action(function (Pedidos $record): void {
                        $record->update(['idSituacao' => 5]);
                    }),

                Action::make('retornar_analise')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->color('success')
                    ->hiddenLabel()
                    ->tooltip('Retornar para Em análise')
                    ->requiresConfirmation()
                    ->action(function (Pedidos $record): void {
                        $record->update(['idSituacao' => 6]);
                    }),
            ])
            ->selectCurrentPageOnly()
            ->paginated([25, 50, 100, 'all'])
            ->striped()
            ->deferLoading();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPedidos::route('/'),
            'create' => CreatePedidos::route('/create'),
            'edit' => EditPedidos::route('/{record}/edit'),
            'print' => PrintPedido::route('/{record}/print'),
        ];
    }

    public static function canViewAny(): bool
    {
        return true;
    }
}
