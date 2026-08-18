<?php

namespace App\Filament\Resources\Almoxarifado;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use App\Filament\Resources\Almoxarifado\PedidosResource\Pages\ListPedidos;
use App\Filament\Resources\Almoxarifado\PedidosResource\Pages\CreatePedidos;
use App\Filament\Resources\Almoxarifado\PedidosResource\Pages\EditPedidos;
use App\Filament\Resources\Almoxarifado\PedidosResource\Pages\PrintPedido;
use App\Filament\Clusters\AlmoxarifadoCluster;
use App\Filament\Resources\Almoxarifado\PedidosResource\Pages;
use App\Filament\Support\LegacyFileUrl;
use App\Filament\Support\TableColumns;
use App\Filament\Support\TableDefaults;
use App\Models\Almoxarifado\Pedidos;
use App\Models\Almoxarifado\SituacaoPedido;
use App\Models\Cadastro\DescricaoResumida;
use App\Models\Cadastro\Setores;
use App\Models\UserEgap;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PedidosResource extends Resource
{
    protected static ?string $model = Pedidos::class;
    protected static ?string $cluster = AlmoxarifadoCluster::class;
    protected static ?string $slug = 'pedidos';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $modelLabel = 'Pedido';
    protected static ?string $pluralModelLabel = 'Pedidos';
    protected static ?string $navigationLabel = 'Requisição de Materiais';
    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
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
                            Section::make('Identificação do Pedido')
                                ->description('Protocolo, data e situação atual da requisição.')
                                ->icon('heroicon-o-clipboard-document-list')
                                ->schema([
                                    TextInput::make('num_protocolo')
                                        ->label('Núm. Protocolo')
                                        ->numeric()
                                        ->mask('****.**.***.***')
                                        ->maxLength(255)
                                        ->placeholder('0000.00.000.000')
                                        ->prefixIcon('heroicon-o-hashtag'),

                                    DatePicker::make('date_time')
                                        ->label('Data pedido')
                                        ->default(fn () => now())
                                        ->displayFormat('d/m/Y')
                                        ->native(false)
                                        ->prefixIcon('heroicon-o-calendar'),

                                    Select::make('idSituacao')
                                        ->label('Situação')
                                        ->relationship('situacao', 'Descricao')
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->placeholder('Selecione a situação')
                                        ->prefixIcon('heroicon-o-flag'),
                                ])
                                ->columns([
                                    'default' => 1,
                                    'md' => 3,
                                ]),

                            Section::make('Solicitação')
                                ->description('Quem solicitou o pedido e quem é o responsável pelo atendimento.')
                                ->icon('heroicon-o-user-group')
                                ->schema([
                                    Select::make('Solicitante')
                                        ->label('Solicitante')
                                        ->required()
                                        ->relationship('solicitante_get', 'name')
                                        ->default(fn () => filament()->auth()->id())
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->placeholder('Selecione o solicitante')
                                        ->prefixIcon('heroicon-o-user'),
                                    Select::make('ResponsavelAtendimento')
                                        ->label('Responsável pelo Atendimento')
                                        ->relationship('responsavel_atendimento', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->placeholder('Selecione o responsável')
                                        ->prefixIcon('heroicon-o-user-circle'),
                                ])
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ]),

                            Section::make('Localização')
                                ->description('Unidade judiciária e setor de origem da requisição.')
                                ->icon('heroicon-o-building-office-2')
                                ->schema([
                                    Select::make('UnidadeJudiciaria')
                                        ->label('Unidade Judiciária')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->native(false)
                                        ->placeholder('Selecione a unidade judiciária')
                                        ->prefixIcon('heroicon-o-building-office-2')
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
                                        ->native(false)
                                        ->placeholder('Selecione o setor')
                                        ->prefixIcon('heroicon-o-map-pin')
                                        ->options(fn (Get $get) => Setores::query()
                                            ->when(
                                                $get('UnidadeJudiciaria'),
                                                fn ($query, $codigoPai) => $query->where('CodigoPai', $codigoPai)
                                            )
                                            ->orderBy('Setor')
                                            ->pluck('Setor', 'id')
                                            ->toArray()
                                        )
                                        ->disabled(fn (Get $get) => blank($get('UnidadeJudiciaria')))
                                        ->helperText('Selecione a unidade judiciária para habilitar este campo.'),
                                    Select::make('setor_responsavel')
                                        ->label('Setor Responsável')
                                        ->relationship('setorResponsavel', 'Setor')
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->placeholder('Selecione o setor responsável'),
                                    Select::make('ComplementoSetor')
                                        ->label('Complemento Setor')
                                        ->relationship('complementoSetor', 'descricao')
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->placeholder('Selecione o complemento'),
                                ])
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ]),

                            Section::make('Anexos e Observações')
                                ->description('Documento de apoio, observações e justificativa do pedido.')
                                ->icon('heroicon-o-paper-clip')
                                ->schema([
                                    FileUpload::make('arquivo')
                                        ->label('Arquivo')
                                        ->directory('files/pedidos')
                                        ->disk('public')
                                        ->visibility('public')
                                        ->openable()
                                        ->downloadable()
                                        ->columnSpanFull(),
                                    Textarea::make('Observacao')
                                        ->label('Observação')
                                        ->placeholder('Observações gerais sobre o pedido')
                                        ->columnSpanFull()
                                        ->rows(3),

                                    Textarea::make('justificativa')
                                        ->label('Justificativa')
                                        ->placeholder('Justificativa da requisição')
                                        ->columnSpanFull()
                                        ->rows(3),
                                ])
                                ->columns(1),
                        ]),
                    Tab::make('Itens do Pedido')
                        ->icon('heroicon-m-shopping-cart')
                        ->schema([
                            Section::make('Resumo Financeiro')
                                ->description('Valor total calculado automaticamente a partir dos itens informados.')
                                ->icon('heroicon-o-calculator')
                                ->schema([
                                    TextInput::make('valor_total_pedido')
                                        ->label('Valor Total do Pedido')
                                        ->readOnly()
                                        ->dehydrated(false)
                                        ->default(0)
                                        ->prefix('R$'),
                                ])
                                ->columns(1),

                            Section::make('Itens do Pedido')
                                ->description('Adicione os materiais solicitados, quantidades e valores.')
                                ->icon('heroicon-o-shopping-cart')
                                ->schema([
                                    Repeater::make('itens')
                                        ->relationship('itens')
                                        ->hiddenLabel()
                                        ->addActionLabel('Adicionar novo item')
                                        ->itemLabel(fn (array $state): ?string => $state['material'] ?? null
                                            ? (DescricaoResumida::find($state['material'])?->Descricao ?? 'Item')
                                            : 'Novo item')
                                        ->collapsible()
                                        ->columns(12)
                                        ->live()
                                        ->schema([
                                            Fieldset::make('Material')
                                                ->columns(12)
                                                ->schema([
                                                    Select::make('material')
                                                        ->label('Material')
                                                        ->relationship('materialRel', 'Descricao')
                                                        ->searchable()
                                                        ->preload()
                                                        ->required()
                                                        ->native(false)
                                                        ->placeholder('Selecione o material')
                                                        ->columnSpan(8),
                                                    Select::make('DescricaoDetalhada')
                                                        ->label('Descrição Detalhada')
                                                        ->relationship('descricaoDetalhadaRel', 'descricao_detalhada')
                                                        ->searchable()
                                                        ->preload()
                                                        ->native(false)
                                                        ->placeholder('Selecione a descrição detalhada')
                                                        ->columnSpan(4),
                                                    Textarea::make('ObservacaoItem')
                                                        ->label('Observação do Item')
                                                        ->placeholder('Observações sobre este item')
                                                        ->rows(2)
                                                        ->columnSpanFull(),
                                                ])
                                                ->columnSpanFull(),

                                            Fieldset::make('Quantidades e Valor')
                                                ->columns([
                                                    'default' => 1,
                                                    'sm' => 3,
                                                ])
                                                ->schema([
                                                    TextInput::make('QuantidadeMaterial')
                                                        ->label('Quantidade Solicitada')
                                                        ->required()
                                                        ->numeric()
                                                        ->placeholder('0')
                                                        ->prefixIcon('heroicon-o-shopping-cart')
                                                        ->live(onBlur: true),
                                                    TextInput::make('QuantidadeMaterialAtendida')
                                                        ->label('Qtd. Atendida')
                                                        ->numeric()
                                                        ->placeholder('0')
                                                        ->prefixIcon('heroicon-o-inbox-stack')
                                                        ->live(onBlur: true),
                                                    TextInput::make('valor_material')
                                                        ->label('Valor Material')
                                                        ->numeric()
                                                        ->inputMode('decimal')
                                                        ->prefix('R$')
                                                        ->placeholder('0,00')
                                                        ->live(onBlur: true)
                                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                                            self::atualizarTotalPedido($get, $set);
                                                        }),
                                                ])
                                                ->columnSpanFull(),

                                            Fieldset::make('Validação')
                                                ->columns([
                                                    'default' => 1,
                                                    'sm' => 2,
                                                    'lg' => 4,
                                                ])
                                                ->schema([
                                                    TextInput::make('situacao')
                                                        ->label('Situação')
                                                        ->numeric(),
                                                    TextInput::make('quantidade_validada')
                                                        ->label('Quantidade Validada')
                                                        ->maxLength(255),
                                                    DatePicker::make('data_validacao')
                                                        ->label('Data Validação')
                                                        ->displayFormat('d/m/Y')
                                                        ->native(false)
                                                        ->prefixIcon('heroicon-o-calendar'),
                                                    Select::make('validado_por')
                                                        ->label('Validado Por')
                                                        ->relationship('validadoPor', 'name')
                                                        ->searchable()
                                                        ->preload()
                                                        ->native(false)
                                                        ->placeholder('Selecione o usuário'),
                                                ])
                                                ->columnSpanFull(),

                                            Fieldset::make('Cancelamento')
                                                ->columns([
                                                    'default' => 1,
                                                    'sm' => 2,
                                                ])
                                                ->schema([
                                                    Select::make('cancelado_por')
                                                        ->label('Cancelado Por')
                                                        ->relationship('canceladoPor', 'name')
                                                        ->searchable()
                                                        ->preload()
                                                        ->native(false)
                                                        ->placeholder('Selecione o usuário'),
                                                    DatePicker::make('data_cancelado')
                                                        ->label('Data Cancelamento')
                                                        ->displayFormat('d/m/Y')
                                                        ->native(false)
                                                        ->prefixIcon('heroicon-o-calendar'),
                                                ])
                                                ->columnSpanFull(),

                                            Textarea::make('justificativa')
                                                ->label('Justificativa do Item')
                                                ->placeholder('Justificativa de validação ou cancelamento')
                                                ->rows(2)
                                                ->columnSpanFull(),
                                        ])
                                        ->afterStateUpdated(fn (Get $get, Set $set) => self::atualizarTotalPedido($get, $set))
                                        ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => self::mutateItemData($data))
                                        ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => self::mutateItemData($data))
                                        ->columnSpanFull(),
                                ])
                                ->columns(1),
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
        return TableDefaults::apply($table)
            ->columns([
                TableColumns::text('id', 'Pedido Nº')
                    ->sortable('desc'),

                TableColumns::date('date_time', 'Data pedido'),

                TextColumn::make('arquivo')
                    ->label('Requisição')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => $state ? 'Abrir PDF' : '-')
                    ->url(fn ($record): ?string => LegacyFileUrl::resolve($record->arquivo, config('app.egap')))
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->weight('bold'),

                TableColumns::text('situacao.Descricao', 'Situação'),

                TableColumns::text('solicitante_get.name', 'Solicitante')
                    ->wrap(),

                TableColumns::text('unidade_judiciaria.UnidadeOrganizacional', 'Unidade Judiciária'),

                TableColumns::text('setor_get.Setor', 'Setor'),

                TableColumns::text('Observacao', 'Observação')
                    ->limit(50),

                TableColumns::text('responsavel_atendimento.name', 'Impresso/Atendido por'),
            ])
            ->filters([
                SelectFilter::make('idSituacao')
                    ->label('Situação do Pedido')
                    ->options(
                        SituacaoPedido::whereIn('id', [3,4,6,7])
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
                    ->modalHeading('Anexar Requisição')
                    ->modalIcon('heroicon-m-clipboard')
                    ->modalSubmitActionLabel('Anexar')
                    ->hiddenLabel()
                    ->schema([
                        FileUpload::make('arquivo')
                            ->disk('public')
                            ->required()
                            ->label('Arquivo PDF')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->directory('files/pedidos')
                    ])
                    ->tooltip('Anexar Requisição')
                    ->action(function (Pedidos $record, array $data): void {
                        $record->update([
                            'idSituacao' => 2,
                            'arquivo' => $data['arquivo'],
                        ]);
                    }),

                Action::make('encaminhar_logistica')
                    ->icon('heroicon-m-truck')
                    ->color('warning')
                    ->hiddenLabel()
                    ->modalHeading('Encaminhar para Logística')
                    ->tooltip('Encaminhar para Logística')
                    ->requiresConfirmation()
                    ->action(function (Pedidos $record): void {
                        $record->update(['idSituacao' => 5]);
                    }),

                Action::make('retornar_analise')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->color('success')
                    ->hiddenLabel()
                    ->modalHeading('Retornar para "Em análise"')
                    ->tooltip('Retornar para Em análise')
                    ->requiresConfirmation()
                    ->action(function (Pedidos $record): void {
                        $record->update(['idSituacao' => 6]);
                    }),
            ])
            ->toolbarActions([])
            ->selectCurrentPageOnly()
            ->paginated([25, 50, 100, 'all'])
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
