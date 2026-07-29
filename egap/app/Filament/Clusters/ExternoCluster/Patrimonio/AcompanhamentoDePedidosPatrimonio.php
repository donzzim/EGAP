<?php

namespace App\Filament\Clusters\ExternoCluster\Patrimonio;

use App\Filament\Clusters\ExternoCluster;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;

class AcompanhamentoDePedidosPatrimonio extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $cluster = ExternoCluster::class;
    protected static ?string $navigationGroup = 'Patrimônio';
    protected static ?string $navigationLabel = 'Acompanhamento de Pedidos';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.externo.patrimonio.acompanhamento-de-pedido';

    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }
}
