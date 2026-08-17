<?php

namespace App\Filament\Clusters\ExternoCluster\Groups\Almoxarifado;

use App\Filament\Clusters\ExternoCluster\Utils\AcompanhamentoDePedidosPage;

class AcompanhamentoDePedidosAlmoxarifado extends AcompanhamentoDePedidosPage
{
    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'almoxarifado/acompanhamento-de-pedidos';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static string | \UnitEnum | null $navigationGroup = 'Almoxarifado';

    protected static ?string $navigationLabel = 'Acompanhamento de Pedidos';

    protected static ?string $title = 'Acompanhamento de Pedidos';

    public function component(): string
    {
        return \App\Filament\Clusters\ExternoCluster\Livewire\Almoxarifado\PedidosAlmoxarifadoTable::class;
    }
}
