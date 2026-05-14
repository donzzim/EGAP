<?php

namespace App\Filament\Clusters\PedidosCluster;

use App\Filament\Clusters\PedidosCluster;
use App\Models\Almoxarifado\FasePedido;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class HistoricoPedidos extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $model = FasePedido::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $title = 'Histórico de Pedidos';

    protected static ?string $cluster = PedidosCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationLabel = 'Histórico de Pedidos';

    protected static ?string $slug = 'historico-pedidos';

    protected static string $view = 'filament.pages.pedidos.historico-pedidos';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                FasePedido::query()
            )
            ->columns([
                Tables\Columns\TextColumn::make('date_time')
                    ->label('Data/Hora')
                    ->date(format: 'd/m/Y')
                    ->description(fn (FasePedido $pedido) => date('H:i', strtotime($pedido->date_time)))
                    ->searchable()
                    ->default(' - ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('termoRef.id')
                    ->label('Termo')
                    ->default(' - ')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pedidoRef.id')
                    ->label('Pedido')
                    ->default(' - ')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('itemPedidoRef.id')
                    ->label('Item Pedido')
                    ->default(' - ')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('descricaoResumidaRef.Descricao')
                    ->label('Material')
                    ->default(' - ')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('descricaoDetalhadaRef.descricao_detalhada')
                    ->label('Descrição Detalhada')
                    ->default(' - ')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantidade')
                    ->label('Quantidade')
                    ->default(' - ')
                    ->alignCenter()
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('Descricao')
                    ->label('Descrição')
                    ->default(' - ')
                    ->badge()
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usuarioRef.name')
                    ->label('Usuário')
                    ->default(' - ')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('termo')
                    ->form([
                        TextInput::make('termo_id')
                            ->label('Nº do Termo')
                            ->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['termo_id'],
                            fn ($query, $value) =>
                            $query->whereHas('termoRef', fn ($q) => $q->where('id', $value))
                        );
                    }),

                Tables\Filters\Filter::make('pedido')
                    ->form([
                        TextInput::make('pedido_id')
                            ->label('Nº do Pedido')
                            ->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['pedido_id'],
                            fn ($query, $value) =>
                            $query->whereHas('pedidoRef', fn ($q) => $q->where('id', $value))
                        );
                    }),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent);

    }

}
