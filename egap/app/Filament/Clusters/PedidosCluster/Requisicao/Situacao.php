<?php

namespace App\Filament\Egap\Clusters\PedidosCluster\Requisicao;

use App\Filament\Egap\Clusters\PedidosCluster;
use App\Models\Egap\Almoxarifado\SituacaoPedido;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class Situacao extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $cluster = PedidosCluster::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'Requisição';
    protected static ?string $title = 'Pedidos - Situação';
    protected static ?string $slug = 'situacao-pedidos';
    protected static ?string $navigationLabel = 'Situação';
    protected static string $view = 'egap.filament.pages.pedidos.requisicao.situacao';

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
            ->actions([
                Action::make('delete')
                    ->color('danger')
                    ->label('')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->action(fn (SituacaoPedido $record) => $record->delete())
            ]);
    }
}
