<?php

namespace App\Filament\Clusters\ExternoCluster\Almoxarifado;

use App\Filament\Clusters\ExternoCluster\AcompanhamentoDePedidosPage;

class AcompanhamentoDePedidosAlmoxarifado extends AcompanhamentoDePedidosPage
{
    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'almoxarifado/acompanhamento-de-pedidos';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Almoxarifado';

    protected static ?string $navigationLabel = 'Acompanhamento de Pedidos';

    protected static ?string $title = 'Acompanhamento de Pedidos';

    protected static string $view = 'filament.pages.externo.almoxarifado.acompanhamento-de-pedidos';
}
