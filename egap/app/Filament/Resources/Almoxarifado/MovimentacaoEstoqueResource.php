<?php

namespace App\Filament\Egap\Resources\Almoxarifado;

use App\Filament\Egap\Resources\Almoxarifado\MovimentacaoEstoqueResource\Pages;
use App\Models\Egap\Almoxarifado\Pedidos;
use App\Models\Egap\Almoxarifado\MovimentacaoEstoque;
use App\Models\Egap\Almoxarifado\NotaFiscal;
use App\Models\Egap\Almoxarifado\TipoMovimentacaoNotaFiscal;
use App\Models\Egap\Cadastro\Setores;
use App\Models\UserEgap;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MovimentacaoEstoqueResource extends Resource
{
    protected static ?string $model = MovimentacaoEstoque::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Movimentação de Estoque';
    protected static ?string $modelLabel = 'Movimentação de Estoque';
    protected static ?string $pluralModelLabel = 'Movimentações de Estoque';
    protected static ?string $navigationGroup = 'Almoxarifado';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        DateTimePicker::make('date_time')
                            ->label('Data/Hora')
                            ->required()
                            ->seconds(false)
                            ->default(now()),

                        Select::make('tipo_movimentacao')
                            ->label('Tipo de Movimentação')
                            ->searchable()
                            ->preload()
                            ->options(fn () => TipoMovimentacaoNotaFiscal::query()
                                ->orderBy('descricao')
                                ->pluck('descricao', 'id')
                                ->toArray()
                            ),

                        Select::make('nota_fiscal')
                            ->label('Nota Fiscal')
                            ->searchable()
                            ->preload()
                            ->options(fn () => NotaFiscal::query()
                                ->orderByDesc('id')
                                ->pluck('id', 'id')
                                ->toArray()
                            ),
                    ]),

                Grid::make(3)
                    ->schema([
                        Select::make('material')
                            ->label('Material')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->relationship('materialRel', 'descricao_detalhada'),

                        TextInput::make('quantidade')
                            ->label('Quantidade')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required(),

                        TextInput::make('preco_unitario')
                            ->label('Preço Unitário')
                            ->numeric()
                            ->inputMode('decimal')
                            ->prefix('R$'),
                    ]),

                Grid::make(3)
                    ->schema([
                        TextInput::make('valor_total')
                            ->label('Valor Total')
                            ->numeric()
                            ->inputMode('decimal')
                            ->prefix('R$')
                            ->helperText('Pode ser preenchido manualmente ou calculado automaticamente.'),

                        TextInput::make('quantidade_estoque')
                            ->label('Quantidade em Estoque')
                            ->numeric()
                            ->inputMode('decimal'),

                        TextInput::make('preco_medio_estoque')
                            ->label('Preço Médio Estoque')
                            ->numeric()
                            ->inputMode('decimal')
                            ->prefix('R$'),
                    ]),

                Grid::make(3)
                    ->schema([
                        TextInput::make('valor_total_estoque')
                            ->label('Valor Total Estoque')
                            ->numeric()
                            ->inputMode('decimal')
                            ->prefix('R$'),

                        Select::make('id_setor')
                            ->label('Setor')
                            ->searchable()
                            ->preload()
                            ->options(fn () => Setores::query()
                                ->orderBy('UnidadeOrganizacional')
                                ->pluck('UnidadeOrganizacional', 'id')
                                ->toArray()
                            ),

//                        Select::make('id_pedido')
//                            ->label('Pedido')
//                            ->searchable()
//                            ->preload()
//                            ->options(fn () => Pedido::query()
//                                ->orderByDesc('id')
//                                ->pluck('id', 'id')
//                                ->toArray()
//                            ),
                    ]),

                Grid::make(2)
                    ->schema([
                        Select::make('atualizado_por')
                            ->label('Atualizado por')
                            ->searchable()
                            ->preload()
                            ->options(fn () => UserEgap::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray()
                            ),

                        Placeholder::make('info_calculo')
                            ->label('Observação')
                            ->content('Os campos monetários e quantitativos podem ser usados para rastrear o histórico do estoque e o custo médio.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('date_time')
                    ->label('Data')
                    ->alignCenter()
                    ->toggleable()
                    ->dateTime('d/m/Y')
                    ->sortable(),

                TextColumn::make('notaFiscal.num_documento')
                    ->label('Nota Fiscal')
                    ->alignCenter()
                    ->toggleable()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('materialRel.descricao_detalhada')
                    ->label('Material')
                    ->alignCenter()
                    ->toggleable()
                    ->searchable()
                    ->wrap(),

                TextColumn::make('quantidade')
                    ->label('Qtd.')
                    ->alignCenter()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('preco_unitario')
                    ->label('Preço Unit.')
                    ->alignCenter()
                    ->toggleable()
                    ->money('BRL', true)
                    ->sortable(),

                TextColumn::make('valor_total')
                    ->label('Valor Total')
                    ->alignCenter()
                    ->toggleable()
                    ->money('BRL', true)
                    ->sortable(),

                TextColumn::make('quantidade_estoque')
                    ->label('Qtd. Estoque')
                    ->alignCenter()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('preco_medio_estoque')
                    ->label('Preço Médio')
                    ->alignCenter()
                    ->toggleable()
                    ->money('BRL', true)
                    ->sortable(),

                TextColumn::make('valor_total_estoque')
                    ->label('Total Estoque')
                    ->alignCenter()
                    ->toggleable()
                    ->money('BRL', true)
                    ->sortable(),

                TextColumn::make('setor.UnidadeOrganizacional')
                    ->label('Unidade Judiciária')
                    ->alignCenter()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('setor.Setor')
                    ->label('Setor')
                    ->alignCenter()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('pedido.id')
                    ->label('Pedido')
                    ->alignCenter()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('atualizadoPor.name')
                    ->label('Atualizado por')
                    ->alignCenter()
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
//                Tables\Actions\EditAction::make(),
//                Tables\Actions\ViewAction::make(),
//                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
//                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMovimentacaoEstoques::route('/'),
//            'create' => Pages\CreateMovimentacaoEstoque::route('/create'),
//            'edit' => Pages\EditMovimentacaoEstoque::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
