<?php

namespace App\Filament\Egap\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\SubNavigationPosition;

class PedidosCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-inbox';
    protected static ?string $navigationLabel = 'Pedidos';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
