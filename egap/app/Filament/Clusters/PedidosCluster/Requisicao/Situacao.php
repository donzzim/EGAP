<?php

namespace App\Filament\Clusters\PedidosCluster\Requisicao;

use App\Filament\Clusters\PedidosCluster;
use App\Models\Almoxarifado\SituacaoPedido;
use Filament\Actions\Action;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class Situacao extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $cluster = PedidosCluster::class;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-flag';

    protected static string|\UnitEnum|null $navigationGroup = 'Requisição';

    protected static ?string $title = 'Pedidos - Situação';

    protected static ?string $slug = 'situacao-pedidos';

    protected static ?string $navigationLabel = 'Situação';

    protected string $view = 'filament.pages.pedidos.requisicao.situacao';

    public function table(Table $table): Table
    {
        return $table
            ->query(SituacaoPedido::query())
            ->columns([
                TextColumn::make('id')
                    ->label('#'),
                TextColumn::make('Descricao')
                    ->alignCenter()
                    ->label('Descrição'),
                TextColumn::make('atualizadoPor.name')
                    ->alignCenter()
                    ->badge()
                    ->color('gray')
                    ->label('Usuário'),
            ])
            ->recordActions([
                Action::make('delete')
                    ->color('danger')
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(fn (SituacaoPedido $record) => $record->delete()),
            ]);
    }
}
