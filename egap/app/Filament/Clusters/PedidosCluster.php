<?php

namespace App\Filament\Clusters;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Clusters\Cluster;

class PedidosCluster extends Cluster
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-inbox';
    protected static ?string $navigationLabel = 'Pedidos';

    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
