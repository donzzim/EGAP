<?php

namespace App\Filament\Resources\Almoxarifado;

use App\Filament\Clusters\AlmoxarifadoCluster;
use App\Filament\Resources\Almoxarifado\PedidosResource\Pages;
use App\Models\Almoxarifado\Pedidos;
use App\Models\Almoxarifado\SituacaoPedido;
use App\Models\Cadastro\Setores;
use App\Models\UserEgap;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PedidosResource extends Resource
{
    protected static ?string $model = Pedidos::class;
    protected static ?string $cluster = AlmoxarifadoCluster::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $modelLabel = 'Pedido';
    protected static ?string $pluralModelLabel = 'Pedidos';
    protected static ?string $navigationLabel = 'Requisição de Materiais';
    //protected static ?string $navigationGroup = 'Almoxarifado';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('TabsPedido')
                ->columnSpanFull()
                ->tabs([

                    Tabs\Tab::make('Dados Gerais')
                        ->icon('heroicon-m-information-circle')
                        ->schema([

                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('num_protocolo')
                                        ->label('Núm. Protocolo')
                                        ->numeric()
                                        ->mask('****.**.***.***')
                                        ->maxLength(255),

                                    Forms\Components\DatePicker::make('date_time')
                                        ->label('Data pedido')
                                        ->default(fn () => now())
                                        ->displayFormat('d/m/Y')
                                        ->native(false),

                                    Forms\Components\Select::make('idSituacao')
                                        ->label('Situação')
                                        ->relationship('situacao', 'Descricao')
                                        ->searchable()
                                        ->preload()
                                        ->native(false),
                                ]),

                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('Solicitante')
                                        ->label('Solicitante')
                                        ->required()
                                        ->relationship('solicitante_get', 'name')
//                                          Ver depois como que deixa o nome do usuário logado como default
//                                        ->default(fn () => filament()->auth()->id())
                                        ->searchable()
                                        ->preload()
                                        ->native(false),

                                    Forms\Components\Select::make('ResponsavelAtendimento')
                                        ->label('Responsável pelo Atendimento')
                                        ->relationship('responsavel_atendimento', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->native(false),
                                ]),

                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('UnidadeJudiciaria')
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

                                    Forms\Components\Select::make('Setor')
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

                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('setor_responsavel')
                                        ->label('Setor Responsável')
                                        ->relationship('setorResponsavel', 'Setor')
                                        ->searchable()
                                        ->preload()
                                        ->native(false),

                                    Forms\Components\Select::make('ComplementoSetor')
                                        ->label('Complemento Setor')
                                        ->relationship('complementoSetor', 'descricao')
                                        ->searchable()
                                        ->preload()
                                        ->native(false),
                                ]),

                            Forms\Components\FileUpload::make('arquivo')
                                ->label('Arquivo')
                                ->directory('pedidos')
                                ->disk('public')
                                ->visibility('public')
                                ->columnSpanFull(),

                            Forms\Components\Textarea::make('Observacao')
                                ->label('Observação')
                                ->columnSpanFull()
                                ->rows(3),

                            Forms\Components\Textarea::make('justificativa')
                                ->label('Justificativa')
                                ->columnSpanFull()
                                ->rows(3),
                        ]),

                    Tabs\Tab::make('Itens do Pedido')
                        ->icon('heroicon-m-shopping-cart')
                        ->schema([

                            Forms\Components\TextInput::make('valor_total_pedido')
                                ->label('Valor Total do Pedido')
                                ->readOnly()
                                ->dehydrated(false)
                                ->default(0)
                                ->prefix('R$')
                                ->extraInputAttributes(['class' => 'text-xl font-bold']),

                            Forms\Components\Repeater::make('itens')
                                ->relationship('itens')
                                ->label('Itens do Pedido')
                                ->addActionLabel('Adicionar novo item')
                                ->columns(12)
                                ->live()
                                ->schema([

                                    Forms\Components\Select::make('material')
                                        ->label('Material')
                                        ->relationship('materialRel', 'Descricao')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->native(false)
                                        ->columnSpan(6),

                                    Forms\Components\TextInput::make('QuantidadeMaterial')
                                        ->label('Quantidade Solicitada')
                                        ->required()
                                        ->numeric()
                                        ->live(onBlur: true)
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('QuantidadeMaterialAtendida')
                                        ->label('Qtd. Atendida')
                                        ->numeric()
                                        ->live(onBlur: true)
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('valor_material')
                                        ->label('Valor Material')
                                        ->numeric()
                                        ->inputMode('decimal')
                                        ->prefix('R$')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            self::atualizarTotalPedido($get, $set);
                                        })
                                        ->columnSpan(2),

                                    Forms\Components\Textarea::make('ObservacaoItem')
                                        ->label('Observação do Item')
                                        ->rows(2)
                                        ->columnSpan(6),

                                    Forms\Components\Select::make('DescricaoDetalhada')
                                        ->label('Descrição Detalhada')
                                        ->relationship('descricaoDetalhadaRel', 'descricao_detalhada')
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->columnSpan(4),

                                    Forms\Components\TextInput::make('situacao')
                                        ->label('Situação')
                                        ->numeric()
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('quantidade_validada')
                                        ->label('Quantidade Validada')
                                        ->maxLength(255)
                                        ->columnSpan(3),

                                    Forms\Components\DatePicker::make('data_validacao')
                                        ->label('Data Validação')
                                        ->displayFormat('d/m/Y')
                                        ->native(false)
                                        ->columnSpan(3),

                                    Forms\Components\Select::make('validado_por')
                                        ->label('Validado Por')
                                        ->relationship('validadoPor', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->columnSpan(3),

                                    Forms\Components\Select::make('cancelado_por')
                                        ->label('Cancelado Por')
                                        ->relationship('canceladoPor', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->columnSpan(3),

                                    Forms\Components\DatePicker::make('data_cancelado')
                                        ->label('Data Cancelamento')
                                        ->displayFormat('d/m/Y')
                                        ->native(false)
                                        ->columnSpan(3),

                                    Forms\Components\Textarea::make('justificativa')
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
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Pedido Nº')
                    ->alignCenter()
                    ->sortable('desc')
                    ->searchable(),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Data pedido')
                    ->date('d/m/Y')
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('arquivo')
                    ->label('Requisição')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => $state ? 'Abrir PDF' : '-')
                    ->url(function ($record) {
                        if (!$record->arquivo) {
                            return null;
                        }

                        return 'https://sistemas.tjes.jus.br/patrimonio' . $record->arquivo;
                    })
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('situacao.Descricao')
                    ->label('Situação')
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('solicitante_get.name')
                    ->label('Solicitante')
                    ->alignCenter()
                    ->default(' - ')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('unidade_judiciaria.UnidadeOrganizacional')
                    ->label('Unidade Judiciária')
                    ->alignCenter()
                    ->default(' - ')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('setor_get.Setor')
                    ->label('Setor')
                    ->alignCenter()
                    ->default(' - ')
                    ->sortable()
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('Observacao')
                    ->label('Observação')
                    ->limit(50)
                    ->alignCenter()
                    ->default(" - ")
                    ->sortable(),

                Tables\Columns\TextColumn::make('responsavel_atendimento.name')
                    ->label('Impresso/Atendido por')
                    ->alignCenter()
                    ->default(" - ")
                    ->sortable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('idSituacao')
                    ->label('Situação do Pedido')
                    ->options(
                        SituacaoPedido::whereIn('id', [3,4,6,7])
                            ->pluck('Descricao', 'id')
                            ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->native(false),

                Tables\Filters\SelectFilter::make('ResponsavelAtendimento')
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

                Tables\Filters\SelectFilter::make('itens')
                    ->label('Material')
                    ->relationship(
                        'itens.materialRel',
                        'Descricao',
                        fn ($query) => $query->where('id_tipo_material', 'C')
                    )
                    ->searchable()
                    ->preload()
                    ->native(false),

                Tables\Filters\SelectFilter::make('UnidadeJudiciaria')
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

                Tables\Filters\Filter::make('numero_pedido')
                    ->label('Nº Pedido')
                    ->form([
                        Forms\Components\TextInput::make('pedido')
                            ->label('Nº Pedido')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['pedido'] ?? null),
                            fn (Builder $query) => $query->where('id', $data['pedido'])
                        );
                    }),

                Tables\Filters\Filter::make('data_validacao')
                    ->label('Data Validação')
                    ->form([
                        Forms\Components\DatePicker::make('data_validacao')
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
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(6)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->hiddenLabel()
                    ->tooltip('Visualizar'),

                Tables\Actions\Action::make('impressao')
                    ->label('Impressão')
                    ->icon('heroicon-m-printer')
                    ->color('danger')
                    ->hiddenLabel()
                    ->tooltip('Impressão')
//                    ->url(fn (Pedidos $record): string => route('impressao_pedido', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('anexar_requisicao')
                    ->icon('heroicon-m-paper-clip')
                    ->color('info')
                    ->hiddenLabel()
                    ->tooltip('Anexar Requisição')
                    ->requiresConfirmation()
                    ->action(function (Pedidos $record): void {
                        $record->update(['idSituacao' => 2]);
                    }),

                Tables\Actions\Action::make('encaminhar_logistica')
                    ->icon('heroicon-m-truck')
                    ->color('warning')
                    ->hiddenLabel()
                    ->tooltip('Encaminhar para Logística')
                    ->requiresConfirmation()
                    ->action(function (Pedidos $record): void {
                        $record->update(['idSituacao' => 5]);
                    }),

                Tables\Actions\Action::make('retornar_analise')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->color('success')
                    ->hiddenLabel()
                    ->tooltip('Retornar para Em análise')
                    ->requiresConfirmation()
                    ->action(function (Pedidos $record): void {
                        $record->update(['idSituacao' => 6]);
                    }),

//                Tables\Actions\EditAction::make()
//                    ->hiddenLabel()
//                    ->tooltip('Editar'),
//
//                Tables\Actions\DeleteAction::make()
//                    ->hiddenLabel()
//                    ->tooltip('Excluir'),
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
            ])
            ->selectCurrentPageOnly()
            ->paginated([50, 100, 150, 200, 'all'])
            ->defaultPaginationPageOption(50)
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
            'index' => Pages\ListPedidos::route('/'),
            'create' => Pages\CreatePedidos::route('/create'),
            'edit' => Pages\EditPedidos::route('/{record}/edit'),
            'print' => Pages\PrintPedido::route('/{record}/print'),
        ];
    }

    public static function canViewAny(): bool
    {
        return true;
    }
}
