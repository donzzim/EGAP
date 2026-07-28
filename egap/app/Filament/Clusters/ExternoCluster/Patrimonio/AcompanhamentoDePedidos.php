<?php

namespace App\Filament\Clusters\ExternoCluster\Patrimonio;

use App\Filament\Clusters\ExternoCluster;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;

class AcompanhamentoDePedidos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $cluster = ExternoCluster::class;
    protected static ?string $navigationGroup = 'Patrimônio';
    protected static ?string $navigationLabel = 'Acompanhamento de Pedidos';

    protected static string $view = 'filament.pages.externo.patrimonio.acompanhamento-de-pedido';

    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }
}
