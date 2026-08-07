<?php

namespace App\Filament\Clusters;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Clusters\Cluster;

class AlmoxarifadoCluster extends Cluster
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationLabel = 'Almoxarifado';

    protected static ?\Filament\Pages\Enums\SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
