<?php

namespace App\Filament\Clusters;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Clusters\Cluster;

class PatrimonioCluster extends Cluster
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Patrimônio';

    protected static ?string $clusterBreadcrumb = 'Patrimônio';

    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;
}
