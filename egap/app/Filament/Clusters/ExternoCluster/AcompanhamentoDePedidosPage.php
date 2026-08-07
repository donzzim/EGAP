<?php

namespace App\Filament\Clusters\ExternoCluster;

use App\Filament\Clusters\ExternoCluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;

abstract class AcompanhamentoDePedidosPage extends Page
{
    protected static ?string $cluster = ExternoCluster::class;

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
