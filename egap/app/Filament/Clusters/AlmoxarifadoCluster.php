<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\SubNavigationPosition;

class AlmoxarifadoCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationLabel = 'Almoxarifado';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
