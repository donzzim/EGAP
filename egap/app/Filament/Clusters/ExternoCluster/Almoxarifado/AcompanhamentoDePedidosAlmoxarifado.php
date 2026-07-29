<?php

namespace App\Filament\Clusters\ExternoCluster\Almoxarifado;

use App\Filament\Clusters\ExternoCluster;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Enums\MaxWidth;

class AcompanhamentoDePedidosAlmoxarifado extends Page
{
    protected static ?int $navigationSort = 3;

    protected static ?string $cluster = ExternoCluster::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Almoxarifado';

    protected static ?string $navigationLabel = 'Acompanhamento de Pedidos';

    protected static ?string $title = 'Acompanhamento de Pedidos';

    protected static string $view = 'filament.pages.externo.almoxarifado.acompanhamento-de-pedidos';

    public function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
}
