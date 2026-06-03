<?php

namespace App\Filament\Resources\Almoxarifado;

use App\Filament\Clusters\AlmoxarifadoCluster;
use App\Filament\Resources\Almoxarifado\NotaFiscalResource\Pages;
use App\Models\Almoxarifado\NotaFiscal;
use App\Models\Cadastro\Setores;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotaFiscalResource extends Resource
{
    protected static ?string $model = NotaFiscal::class;

    protected static ?string $cluster = AlmoxarifadoCluster::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $modelLabel = 'Nota Fiscal';
    protected static ?string $pluralModelLabel = 'Notas Fiscais';
    protected static ?string $navigationLabel = 'Nota Fiscal';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('TabsNotaFiscal')
                ->columnSpanFull()
                ->tabs([
                    Tabs\Tab::make('Dados Gerais')
                        ->icon('heroicon-m-information-circle')
                        ->schema([

                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('num_documento')
                                        ->numeric()
                                        ->required()
                                        ->label('Núm. Documento'),

                                    Forms\Components\Select::make('tipo_documento')
                                        ->label('Tipo Documento')
                                        ->required()
                                        ->relationship('tipoDocumento', 'descricao')
                                        ->searchable()
                                        ->preload()
                                        ->native(false),

                                    Forms\Components\DatePicker::make('data_documento')
                                        ->label('Data Documento')
                                        ->default(now())
                                        ->required()
                                        ->displayFormat('d/m/Y')
                                        ->native(false),
                                ]),

                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\Select::make('fornecedor')
                                        ->label('Fornecedor')
                                        ->required()
                                        ->relationship('fornecedorRef', 'NomeFornecedor')
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->columnSpan(2),

                                    Forms\Components\Select::make('estoque')
                                        ->label('Estoque')
                                        ->required()
                                        ->options([
                                            '1' => 'Entrada no Estoque',
                                            '2' => 'Saída Imediata',
                                        ])
                                        ->native(false),
                                ]),

                            Forms\Components\Grid::make(2)
                                ->schema([

                                    Forms\Components\Select::make('unidade_judiciaria')
                                        ->label('Unidade Judiciária')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->options(fn () => Setores::query()
                                            ->whereColumn('id', 'CodigodaUO')
                                            ->orderBy('UnidadeOrganizacional')
                                            ->pluck('UnidadeOrganizacional', 'CodigoPai')
                                            ->toArray()
                                        )
                                        ->afterStateUpdated(fn (Set $set) => $set('setor', null)),

                                    Forms\Components\Select::make('setor')
                                        ->label('Setor')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->options(fn (Get $get) => Setores::query()
                                            ->when(
                                                $get('unidade_judiciaria'),
                                                fn ($query, $codigoPai) => $query->where('CodigoPai', $codigoPai)
                                            )
                                            ->orderBy('Setor')
                                            ->pluck('Setor', 'id')
                                            ->toArray()
                                        )
                                        ->disabled(fn (Get $get) => blank($get('unidade_judiciaria'))),
                                ]),

                            Forms\Components\Textarea::make('observacao')
                                ->label('Observação')
                                ->required()
                                ->columnSpanFull()
                                ->rows(3),
                        ]),

                    Tabs\Tab::make('Itens da Nota')
                        ->icon('heroicon-m-shopping-cart')
                        ->schema([

                            Forms\Components\TextInput::make('valor_total')
                                ->numeric()
                                ->readOnly()
                                ->dehydrated()
                                ->default(0)
                                ->prefix('R$')
                                ->label('Subtotal / Total da Nota Fiscal')
                                ->extraInputAttributes(['class' => 'text-xl font-bold']),

                            Forms\Components\Repeater::make('itens')
                                ->relationship('itens')
                                ->label('Itens da Nota')
                                ->addActionLabel('Adicionar novo material')
                                ->columns(12)
                                ->live()
                                ->schema([
                                    Forms\Components\Select::make('id_material')
                                        ->label('Material')
                                        ->relationship('material', 'descricao_detalhada')
                                        ->searchable()
                                        ->required()
                                        ->native(false)
                                        ->columnSpan(6),

                                    Forms\Components\TextInput::make('quantidade')
                                        ->label('Quantidade')
                                        ->required()
                                        ->numeric()
                                        ->inputMode('decimal')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            self::atualizarTotalItem($get, $set);
                                            self::atualizarTotalNota($get, $set);
                                        })
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('preco_unitario')
                                        ->label('Preço Unitário')
                                        ->required()
                                        ->numeric()
                                        ->inputMode('decimal')
                                        ->prefix('R$')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            self::atualizarTotalItem($get, $set);
                                            self::atualizarTotalNota($get, $set);
                                        })
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('total_item')
                                        ->label('Total Item')
                                        ->numeric()
                                        ->readOnly()
                                        ->dehydrated()
                                        ->prefix('R$')
                                        ->columnSpan(2),

                                ])
                                ->afterStateUpdated(fn (Get $get, Set $set) => self::atualizarTotalNota($get, $set))
                                ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => self::mutateItemData($data))
                                ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => self::mutateItemData($data))
                                ->columnSpanFull(),
                        ]),

                    Tabs\Tab::make('Situação')
                        ->icon('heroicon-m-check-badge')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([

                                    Forms\Components\Select::make('situacao')
                                        ->label('Situação')
                                        ->required()
                                        ->relationship('situacaoRef', 'descricao')
                                        ->native(false),

                                    Forms\Components\DatePicker::make('data_situacao')
                                        ->label('Data da Situação')
                                        ->required()
                                        ->displayFormat('d/m/Y')
                                        ->native(false),

                                ]),
                        ]),
                ]),
        ]);
    }

    public static function mutateItemData(array $data): array
    {
        $quantidade = self::normalizarValorMonetario($data['quantidade'] ?? 0);
        $precoUnitario = self::normalizarValorMonetario($data['preco_unitario'] ?? 0);

        $data['quantidade'] = number_format($quantidade, 2, '.', '');
        $data['preco_unitario'] = number_format($precoUnitario, 2, '.', '');
        $data['total_item'] = number_format($quantidade * $precoUnitario, 2, '.', '');
        $data['atualizado_por'] = auth()->id();
        $data['date_time'] = now();

        return $data;
    }

    public static function atualizarTotalItem(Get $get, Set $set): void
    {
        $quantidade = self::normalizarValorMonetario($get('quantidade'));
        $precoUnitario = self::normalizarValorMonetario($get('preco_unitario'));

        $set('total_item', number_format($quantidade * $precoUnitario, 2, '.', ''));
    }

    public static function atualizarTotalNota(Get $get, Set $set): void
    {
        $itens = $get('../../itens') ?? [];

        $totalGeral = collect($itens)
            ->sum(fn (array $item) => self::normalizarValorMonetario($item['total_item'] ?? 0));

        $set('../../valor_total', number_format($totalGeral, 2, '.', ''));
    }

    public static function calcularValorTotal(array $itens): string
    {
        $total = collect($itens)
            ->sum(fn (array $item) =>
                self::normalizarValorMonetario($item['quantidade'] ?? 0) *
                self::normalizarValorMonetario($item['preco_unitario'] ?? 0)
            );

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
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('num_documento')
                    ->label('Núm. documento')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('data_documento')
                    ->label('Data documento')
                    ->alignCenter()
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('fornecedorRef.NomeFornecedor')
                    ->label('Fornecedor')
                    ->default(' - ')
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Total da Nota')
                    ->money('BRL', true)
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('itens.material.descricao_detalhada')
                    ->label('Material')
                    ->state(fn ($record) => $record->itens
                        ->pluck('material.descricao_detalhada')
                        ->toArray()
                    )
                    ->listWithLineBreaks()
                    ->alignCenter()
                    ->wrap()
                    ->limit(40),

                Tables\Columns\TextColumn::make('itens.quantidade')
                    ->label('Quantidade')
                    ->state(fn (NotaFiscal $record): array => $record->itens->pluck('quantidade')->toArray())
                    ->listWithLineBreaks()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('itens.preco_unitario')
                    ->label('Preço unitário')
                    ->state(fn (NotaFiscal $record): array => $record->itens
                        ->map(fn ($item) => 'R$ ' . number_format((float) $item->preco_unitario, 2, ',', '.'))
                        ->toArray())
                    ->listWithLineBreaks()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_item_calculado')
                    ->label('Total item')
                    ->state(fn (NotaFiscal $record): array => $record->itens
                        ->map(fn ($item) => 'R$ ' . number_format((float) $item->preco_unitario * (float) $item->quantidade, 2, ',', '.'))
                        ->toArray())
                    ->listWithLineBreaks()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('situacaoRef.descricao')
                    ->label('Situação')
                    ->alignCenter()
                    ->default(' - ')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->tooltip('Editar')
                    ->hiddenLabel(),
                Tables\Actions\ViewAction::make()
                    ->tooltip('Visualizar')
                    ->hiddenLabel(),
                Tables\Actions\DeleteAction::make()
                    ->tooltip('Excluir')
                    ->modalHeading('Excluir registro')
                    ->hiddenLabel(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListNotaFiscals::route('/'),
            'create' => Pages\CreateNotaFiscal::route('/create'),
            'edit' => Pages\EditNotaFiscal::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return true;
    }
}
