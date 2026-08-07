<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;

class PedidosCluster extends Cluster
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox';

    protected static ?string $navigationLabel = 'Pedidos';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
