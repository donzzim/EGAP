<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;

class AlmoxarifadoCluster extends Cluster
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationLabel = 'Almoxarifado';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
