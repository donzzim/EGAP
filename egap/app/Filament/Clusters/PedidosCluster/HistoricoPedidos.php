<?php

namespace App\Filament\Clusters\PedidosCluster;

use App\Filament\Clusters\PedidosCluster;
use App\Models\Almoxarifado\FasePedido;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class HistoricoPedidos extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $model = FasePedido::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $title = 'Histórico de Pedidos';

    protected static ?string $cluster = PedidosCluster::class;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationLabel = 'Histórico de Pedidos';

    protected static ?string $slug = 'historico-pedidos';

    protected string $view = 'filament.pages.pedidos.historico-pedidos';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                FasePedido::query()
            )
            ->columns([
                TextColumn::make('date_time')
                    ->label('Data/Hora')
                    ->date(format: 'd/m/Y')
                    ->description(fn (FasePedido $pedido) => date('H:i', strtotime($pedido->date_time)))
                    ->searchable()
                    ->default(' - ')
                    ->sortable(),
                TextColumn::make('termoRef.id')
                    ->label('Termo')
                    ->default(' - ')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pedidoRef.id')
                    ->label('Pedido')
                    ->default(' - ')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('itemPedidoRef.id')
                    ->label('Item Pedido')
                    ->default(' - ')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('descricaoResumidaRef.Descricao')
                    ->label('Material')
                    ->default(' - ')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('descricaoDetalhadaRef.descricao_detalhada')
                    ->label('Descrição Detalhada')
                    ->default(' - ')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantidade')
                    ->label('Quantidade')
                    ->default(' - ')
                    ->alignCenter()
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('Descricao')
                    ->label('Descrição')
                    ->default(' - ')
                    ->badge()
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usuarioRef.name')
                    ->label('Usuário')
                    ->default(' - ')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('termo')
                    ->schema([
                        TextInput::make('termo_id')
                            ->label('Nº do Termo')
                            ->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['termo_id'],
                            fn ($query, $value) => $query->whereHas('termoRef', fn ($q) => $q->where('id', $value))
                        );
                    }),

                Filter::make('pedido')
                    ->schema([
                        TextInput::make('pedido_id')
                            ->label('Nº do Pedido')
                            ->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['pedido_id'],
                            fn ($query, $value) => $query->whereHas('pedidoRef', fn ($q) => $q->where('id', $value))
                        );
                    }),
            ])
            ->filtersLayout(FiltersLayout::AboveContent);

    }
}
