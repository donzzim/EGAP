<?php

namespace App\Filament\Resources\Patrimonio\BensImoveis;

use App\Filament\Clusters\PatrimonioCluster;
use App\Filament\Resources\Patrimonio\BensImoveis\TributoResource\Pages;
use App\Models\Patrimonio\BensImoveis\Tributo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;

class TributoResource extends Resource
{
    protected static ?string $cluster = PatrimonioCluster::class;

protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $model = Tributo::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Tributos';
    protected static ?string $modelLabel = 'Tributo';
    protected static ?string $pluralModelLabel = 'Tributos';
    protected static ?string $navigationGroup = 'Bens Imóveis';
    protected static ?int $navigationSort = 6;
    protected static ?string $slug = 'bens-imoveis/tributos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Tributos')
                            ->schema([
                                Forms\Components\Select::make('Id_imovel')
                                    ->label('Imóvel')
                                    ->relationship('imovelRelacaoref', 'descricao')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('tipo_tributo')
                                    ->label('Tipo do tributo')
                                    ->relationship('tipoTributoRelacaoref', 'descricao')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\DatePicker::make('vencimento')
                                    ->label('Vencimento')
                                    ->displayFormat('d/m/Y'),

                                Forms\Components\TextInput::make('valor')
                                    ->label('Valor')
                                    ->numeric(),

                                Forms\Components\DatePicker::make('pago_em')
                                    ->label('Pago em')
                                    ->displayFormat('d/m/Y'),

                                Forms\Components\TextInput::make('valor_pago')
                                    ->label('Valor Pago')
                                    ->numeric(),

                                Forms\Components\TextInput::make('processo_pagto')
                                    ->label('Processo Pagto'),

                                Forms\Components\Select::make('atualizado_por')
                                    ->label('Atualizado por')
                                    ->relationship('atualizadoPorRelacaoref', 'name')
                                    ->default(fn () => auth()->id())
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Textarea::make('observacao')
                                    ->label('Observação')
                                    ->rows(4),

                                Forms\Components\DatePicker::make('date_time')
                                    ->label('Atualizado em')
                                    ->default(now())
                                    ->displayFormat('d/m/Y')
                                    ->disabled()
                                    ->dehydrated(),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Eventos')
                            ->schema([
                                Forms\Components\Repeater::make('eventos')
                                    ->schema([
                                        Forms\Components\DatePicker::make('data')
                                            ->label('Data')
                                            ->displayFormat('d/m/Y'),

                                        Forms\Components\Textarea::make('descricao')
                                            ->label('Descrição')
                                            ->rows(2),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(1)
                                    ->addActionLabel('Adicionar em eventos')
                                    ->hiddenLabel()
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('imovelRelacaoref.descricao')
                    ->label('Imóvel')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tipoTributoRelacaoref.descricao')
                    ->label('Tipo do tributo')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('vencimento')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('valor')
                    ->label('Valor')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('pago_em')
                    ->label('Pago em')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('valor_pago')
                    ->label('Valor Pago')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('processo_pagto')
                    ->label('Processo Pagto')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('observacao')
                    ->label('Observação')
                    ->sortable()
                    ->searchable()
                    ->width('400px')
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->color('warning')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Editar Tributo')
                    ->modalWidth('4xl')
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['date_time'] = date('Y-m-d');

                        if (empty($data['eventos'])) {
                            $data['eventos'] = [['data' => null, 'descricao' => null]];
                        }

                        return $data;
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Excluir')
                    ->color('danger')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir Selecionados'),
                ]),
            ])
            ->searchPlaceholder('Entre com a palavra-chave')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->striped()
            ->emptyStateHeading('Nenhum Tributo encontrado');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTributos::route('/'),
        ];
    }
}
