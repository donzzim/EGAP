<?php

namespace App\Filament\Clusters\ExternoCluster;

use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Enums\Width;
use App\Filament\Clusters\ExternoCluster;
use Filament\Pages\Page;

abstract class AcompanhamentoDePedidosPage extends Page
{
    protected static ?string $cluster = ExternoCluster::class;

    function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
