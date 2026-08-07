<?php

namespace App\Filament\Clusters;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Clusters\Cluster;

class ExternoCluster extends Cluster
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-window';
    protected static ?string $navigationLabel = 'Ambiente Externo';
    protected static ?string $clusterBreadcrumb = 'Ambiente Externo';
    protected static ?int $navigationSort = 10;

    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
