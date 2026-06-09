<?php

namespace App\Filament\Resources\Almoxarifado;

use App\Filament\Clusters\AlmoxarifadoCluster;
use App\Filament\Resources\Almoxarifado\MovimentacaoEstoqueResource\Pages;
use App\Models\Almoxarifado\MovimentacaoEstoque;
use App\Models\Almoxarifado\NotaFiscal;
use App\Models\Almoxarifado\TipoMovimentacaoNotaFiscal;
use App\Models\Cadastro\Setores;
use App\Models\User;
use App\Models\UserEgap;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MovimentacaoEstoqueResource extends Resource
{
    protected static ?string $model = MovimentacaoEstoque::class;

    protected static ?string $cluster = AlmoxarifadoCluster::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Movimentação de Estoque';

    protected static ?string $modelLabel = 'Movimentação de Estoque';

    protected static ?string $pluralModelLabel = 'Movimentações de Estoque';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
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
                            ->default(now()),

                        Select::make('tipo_movimentacao')
                            ->label('Tipo de Movimentação')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Selecione o tipo')
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
                            ->required(),

                        TextInput::make('preco_unitario')
                            ->label('Preço Unitário')
                            ->required()
                            ->columnSpan(1)
                            ->inputMode('decimal')
                            ->prefix('R$')
                            ->mask(RawJs::make('$money($input, \',\', \'.\', 2)'))
                            ->stripCharacters('.')
                            ->formatStateUsing(fn (float|int|string|null $state): ?string => static::formatarValorMonetario($state))
                            ->dehydrateStateUsing(fn (float|int|string|null $state): ?string => static::normalizarValorMonetario($state))
                            ->placeholder('0,00'),

                        TextInput::make('valor_total')
                            ->label('Valor Total')
                            ->required()
                            ->columnSpan(1)
                            ->inputMode('decimal')
                            ->prefix('R$')
                            ->mask(RawJs::make('$money($input, \',\', \'.\', 2)'))
                            ->stripCharacters('.')
                            ->formatStateUsing(fn (float|int|string|null $state): ?string => static::formatarValorMonetario($state))
                            ->dehydrateStateUsing(fn (float|int|string|null $state): ?string => static::normalizarValorMonetario($state))
                            ->placeholder('0,00')
                            ->helperText('Pode ser preenchido manualmente ou calculado automaticamente.'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 3,
                    ]),

                Section::make('Saldo em estoque')
                    ->description('Registre a posição do estoque após a movimentação para manter o histórico de custo médio.')
                    ->icon('heroicon-o-chart-bar-square')
                    ->schema([
                        TextInput::make('quantidade_estoque')
                            ->label('Quantidade em Estoque')
                            ->required()
                            ->numeric()
                            ->inputMode('decimal')
                            ->placeholder('0,00'),

                        TextInput::make('preco_medio_estoque')
                            ->label('Preço Médio Estoque')
                            ->required()
                            ->inputMode('decimal')
                            ->prefix('R$')
                            ->mask(RawJs::make('$money($input, \',\', \'.\', 2)'))
                            ->stripCharacters('.')
                            ->formatStateUsing(fn (float|int|string|null $state): ?string => static::formatarValorMonetario($state))
                            ->dehydrateStateUsing(fn (float|int|string|null $state): ?string => static::normalizarValorMonetario($state))
                            ->placeholder('0,00'),

                        TextInput::make('valor_total_estoque')
                            ->label('Valor Total Estoque')
                            ->required()
                            ->inputMode('decimal')
                            ->prefix('R$')
                            ->mask(RawJs::make('$money($input, \',\', \'.\', 2)'))
                            ->stripCharacters('.')
                            ->formatStateUsing(fn (float|int|string|null $state): ?string => static::formatarValorMonetario($state))
                            ->dehydrateStateUsing(fn (float|int|string|null $state): ?string => static::normalizarValorMonetario($state))
                            ->placeholder('0,00'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 3,
                    ]),

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

    protected static function formatarValorMonetario(float|int|string|null $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return number_format((float) static::normalizarValorMonetario($valor), 2, ',', '');
    }

    protected static function normalizarValorMonetario(float|int|string|null $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $valor = preg_replace('/[^\d,.-]/', '', (string) $valor) ?? '0';

        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return number_format((float) $valor, 2, '.', '');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Nenhum registro encontrado')
            ->defaultPaginationPageOption(25)
            ->query(
                static::getEloquentQuery()->latest('id')
            )
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('tipoMovimentacaoRel.descricao')
                    ->label('Tipo Mov.')
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('date_time')
                    ->label('Data')
                    ->alignCenter()
                    ->dateTime('d/m/Y')
                    ->sortable(),

                TextColumn::make('notaFiscal.num_documento')
                    ->label('Nota Fiscal')
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('materialRel.descricao_detalhada')
                    ->label('Material')
                    ->alignCenter()
                    ->searchable()
                    ->wrap(),

                TextColumn::make('quantidade')
                    ->label('Qtd.')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('preco_unitario')
                    ->label('Preço Unit.')
                    ->alignCenter()
                    ->money('BRL', true)
                    ->sortable(),

                TextColumn::make('valor_total')
                    ->label('Valor Total')
                    ->alignCenter()
                    ->money('BRL', true)
                    ->sortable(),

                TextColumn::make('quantidade_estoque')
                    ->label('Qtd. Estoque')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('preco_medio_estoque')
                    ->label('Preço Médio')
                    ->alignCenter()
                    ->money('BRL', true)
                    ->sortable(),

                TextColumn::make('valor_total_estoque')
                    ->label('Total Estoque')
                    ->alignCenter()
                    ->money('BRL', true)
                    ->sortable(),

                TextColumn::make('setor.UnidadeOrganizacional')
                    ->label('Unidade Judiciária')
                    ->alignCenter()
                    ->searchable(),

                TextColumn::make('setor.Setor')
                    ->label('Setor')
                    ->alignCenter()
                    ->searchable(),

                TextColumn::make('pedido.id')
                    ->label('Pedido')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('atualizadoPor.name')
                    ->label('Atualizado por')
                    ->alignCenter()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
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
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMovimentacaoEstoques::route('/'),
            'create' => Pages\CreateMovimentacaoEstoque::route('/create'),
            'edit' => Pages\EditMovimentacaoEstoque::route('/{record}/edit'),
        ];
    }
}
