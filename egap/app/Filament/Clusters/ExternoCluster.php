<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\SubNavigationPosition;

class ExternoCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-window';
    protected static ?string $navigationLabel = 'Ambiente Externo';
    protected static ?string $clusterBreadcrumb = 'Ambiente Externo';
    protected static ?int $navigationSort = 10;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
