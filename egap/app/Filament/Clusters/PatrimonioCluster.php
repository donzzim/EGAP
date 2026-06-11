<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\SubNavigationPosition;

class PatrimonioCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Patrimônio';

    protected static ?string $clusterBreadcrumb = 'Patrimônio';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;
}
