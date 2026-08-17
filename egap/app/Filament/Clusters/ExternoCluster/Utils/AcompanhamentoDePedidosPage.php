<?php

namespace App\Filament\Clusters\ExternoCluster\Utils;

use App\Filament\Clusters\ExternoCluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;

abstract class AcompanhamentoDePedidosPage extends Page
{
    protected static ?string $cluster = ExternoCluster::class;

    protected string $view = 'livewire.support.table-page';

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    abstract public function component(): string;
}
